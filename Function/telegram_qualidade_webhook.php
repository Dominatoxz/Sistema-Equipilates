<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/telegram_api.php';
require_once __DIR__ . '/notificar_qualidade.php';
require_once __DIR__ . '/qualidade_consultas.php';

header('Content-Type: application/json');

$secretEsperado = getenv('TELEGRAM_QUALIDADE_WEBHOOK_SECRET');
$secretRecebido = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
if (empty($secretEsperado) || !hash_equals($secretEsperado, $secretRecebido)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
    exit;
}

$token = getenv('TELEGRAM_QUALIDADE_BOT_TOKEN');
$update = json_decode(file_get_contents('php://input'), true);
$callback = $update['callback_query'] ?? null;
$db = (new Database())->getConnection();

// RECONSTRUÍDO em 2026-09-09 depois que um deploy externo (git checkout pra
// commit 00d13dc, "Alterações Torre Wall unit, prancha de molas e contagem")
// sobrescreveu Function/ e reverteu este arquivo pra uma versão antiga (sem
// motivo obrigatório, sem os 3 botões, sem /pendentes /validar /reprovados
// /inspetor /ajuda /meuid). Reescrito por completo a partir do histórico da
// conversa.

// Mensagem de texto comum (/start, /status, /pendentes ...) — não é decisão
// de qualidade em si, é comando de quem já está cadastrado.
if (!$callback && isset($update['message']['chat']['id'])) {
    $chatIdMsg = $update['message']['chat']['id'];
    $textoMsg = trim($update['message']['text'] ?? '');

    // Mesmo gate de sempre (chat cadastrado e ativo em qualidade_telegram_chats),
    // agora reaproveitado pelos comandos novos também (Vitor, 2026-09-08:
    // "coloque as funcionalidades que falei sobre a qualidade" — antes só o
    // /status ... qualidade checava isso, ficava repetido se cada comando novo
    // reescrevesse a mesma consulta).
    $estaAutorizado = function () use ($db, $chatIdMsg): bool {
        $stmt = $db->prepare('SELECT 1 FROM qualidade_telegram_chats WHERE chat_id = ? AND ativo = 1');
        $stmt->execute([(string) $chatIdMsg]);
        return (bool) $stmt->fetchColumn();
    };
    $negarNaoAutorizado = function () use ($token, $chatIdMsg): void {
        telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatIdMsg,
            'text' => 'Esse comando é só pra quem já está cadastrado em qualidade_telegram_chats.',
        ]);
        echo json_encode(['ok' => true]);
        exit;
    };

    // Motivo de Retrabalho/Reprovado pendente (depois de clicar um dos dois
    // botões — Vitor, 2026-09-09: "quando recusado deve ter o motivo nas
    // duas tentativas"). Se essa pessoa clicou um dos dois antes e ainda não
    // mandou o motivo, o texto que chegou agora É o motivo — a menos que
    // comece com "/", que é ela desistindo do fluxo (mesmo padrão do bot do
    // ERP, telegram_aguardando_motivo). A decisão só é aplicada de verdade
    // aqui, com aplicarDecisaoNegativa() (função lá embaixo, hoisted) — o
    // $pendMotivo['bucket'] (gravado no clique do botão) diz se é
    // Retrabalho ou Reprovado.
    $stmtPendMotivo = $db->prepare('SELECT * FROM qualidade_aguardando_motivo WHERE chat_id = ?');
    $stmtPendMotivo->execute([(string) $chatIdMsg]);
    $pendMotivo = $stmtPendMotivo->fetch(PDO::FETCH_ASSOC);
    if ($pendMotivo && $textoMsg !== '') {
        $db->prepare('DELETE FROM qualidade_aguardando_motivo WHERE chat_id = ?')->execute([(string) $chatIdMsg]);
        if ($textoMsg[0] !== '/') {
            $fromMsg = $update['message']['from'] ?? [];
            $usuarioTelegramMsg = $fromMsg['username'] ?? ($fromMsg['first_name'] ?? 'desconhecido');
            aplicarDecisaoNegativa($db, $token, $pendMotivo['tabela_origem'], (int) $pendMotivo['item_id'], (int) $pendMotivo['tentativa'], $usuarioTelegramMsg, (string) $chatIdMsg, $textoMsg, $pendMotivo['bucket']);
            echo json_encode(['ok' => true]);
            exit;
        }
        // começou com "/" — desistiu do fluxo de motivo; segue normal pro resto do arquivo.
    }

    // Chat NUNCA visto (nenhuma linha, nem inativa, em qualidade_telegram_chats)
    // — registra 1 solicitação de cadastro pendente, pra avisar o admin do ERP
    // (Vitor, 2026-09-09: "quando uma nova pessoa der start no Bot da qualidade,
    // suba uma solicitação para o administrador do ERP para que ele valide esse
    // novo usuário e defina o seu perfil"). Idempotente — só grava se ainda não
    // tiver uma solicitação em aberto pra esse chat_id; o cron do ERP
    // (telegram_cron.php, bloco "Solicitações de cadastro novo") lê essa tabela
    // e manda o aviso com botões pra já cadastrar. Roda ANTES de qualquer
    // comando, pra pegar até quem nunca vai digitar nada reconhecido.
    $stmtConhecido = $db->prepare('SELECT 1 FROM qualidade_telegram_chats WHERE chat_id = ?');
    $stmtConhecido->execute([(string) $chatIdMsg]);
    if (!$stmtConhecido->fetchColumn()) {
        $stmtPendente = $db->prepare('SELECT 1 FROM qualidade_solicitacoes_cadastro WHERE chat_id = ? AND atendida = 0');
        $stmtPendente->execute([(string) $chatIdMsg]);
        if (!$stmtPendente->fetchColumn()) {
            $fromMsg = $update['message']['from'] ?? [];
            $nomeTg = trim(($fromMsg['first_name'] ?? '') . ' ' . ($fromMsg['last_name'] ?? ''));
            $db->prepare(
                'INSERT INTO qualidade_solicitacoes_cadastro (chat_id, nome_telegram, username_telegram) VALUES (?, ?, ?)'
            )->execute([(string) $chatIdMsg, $nomeTg !== '' ? $nomeTg : null, $fromMsg['username'] ?? null]);
        }
    }

    // /status <id> qualidade  ou  /status OS<id> qualidade — coloca o item
    // em "Aguardando qualidade" manualmente e dispara a mensagem de
    // aprovar/reprovar, sem precisar esperar a bipagem física. Só quem já
    // está cadastrado (qualidade ou liderança) pode usar.
    if (preg_match('/^\/status\s+(OS)?(\d+)\s+qualidade$/i', $textoMsg, $m)) {
        if (!$estaAutorizado()) {
            $negarNaoAutorizado();
        }

        $tabelaCmd = !empty($m[1]) ? 'itens_os' : 'itens_producao';
        $idCmd = (int) $m[2];
        $idExibicaoCmd = ($tabelaCmd === 'itens_os') ? 'OS' . $idCmd : (string) $idCmd;

        $stmtCmd = $db->prepare("UPDATE $tabelaCmd SET status_qualidade = 'Aguardando' WHERE id = :id");
        $stmtCmd->execute([':id' => $idCmd]);

        if ($stmtCmd->rowCount() === 0) {
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => "Item {$idExibicaoCmd} não encontrado.",
            ]);
        } else {
            notificarQualidade($db, $tabelaCmd, $idCmd);
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => "Item {$idExibicaoCmd} colocado em inspeção de qualidade.",
            ]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    // /pendentes (ou /validar sem argumento) — panorama de quem está aguardando
    // validação AGORA, do mais antigo pro mais novo (🔴 = QUALIDADE_DIAS_ALERTA+
    // dias parado). Puramente informativo — não reenvia botão nenhum.
    if ($textoMsg === '/pendentes' || $textoMsg === '/validar') {
        if (!$estaAutorizado()) {
            $negarNaoAutorizado();
        }
        $lista = qualidadeListaAguardando($db);
        if (!$lista) {
            telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => '🎉 Nada aguardando validação agora. Pra rever um pedido específico já decidido, manda /validar NÚMERO.']);
            echo json_encode(['ok' => true]);
            exit;
        }
        $totalItens = array_sum(array_map(fn($p) => count($p['itens']), $lista));
        $texto = "🔬 <b>Validações pendentes</b>\n{$totalItens} item(ns) em " . count($lista) . " pedido(s) — do mais antigo pro mais novo (🔴 = " . QUALIDADE_DIAS_ALERTA . "+ dias parado).\n\n"
            . qualidadeListaAguardandoTexto($lista)
            . "\n\nPra reenviar as pendências de um pedido específico (ou ver o que já foi decidido), manda /validar NÚMERO.";
        telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => $texto, 'parse_mode' => 'HTML']);
        echo json_encode(['ok' => true]);
        exit;
    }

    // /validar NÚMERO — busca direto um pedido (Vitor, 2026-09-08: "ideal é
    // ter um /validacao igual o do ERP" — digitar o número em vez de só olhar
    // a lista). Item ainda Aguardando: REENVIA a mensagem de verdade (mesmo
    // Aprovar/Retrabalho/Reprovado reais, via notificarQualidade — só pra
    // quem digitou o comando, não transmite pros outros cadastrados em
    // qualidade — Vitor/Matheus, 2026-09-10). Item já decidido: mostra só
    // informativo, sem reabrir o fluxo (ver qualidadeItemFichaTexto — reabrir
    // de verdade é /status ID qualidade, que já existe e faz isso do jeito
    // certo, com tentativa/reimpressão).
    if (preg_match('/^\/validar\s+(.+)$/iu', $textoMsg, $mVal)) {
        if (!$estaAutorizado()) {
            $negarNaoAutorizado();
        }
        $numeroVal = trim($mVal[1]);
        $itensVal = qualidadeItensDoPedido($db, $numeroVal);
        if (!$itensVal) {
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => 'Pedido ' . htmlspecialchars($numeroVal) . ' — nenhum item desse pedido passou pelo gate de qualidade ainda.',
            ]);
            echo json_encode(['ok' => true]);
            exit;
        }
        $reenviados = 0;
        $jaComOutro = 0;
        $stmtDonoAtual = $db->prepare(
            "SELECT chat_id FROM qualidade_mensagens_enviadas WHERE tabela_origem = :tabela AND item_id = :id AND tentativa = :tentativa LIMIT 1"
        );
        foreach ($itensVal as $it) {
            if ($it['status_qualidade'] === 'Aguardando') {
                // Só manda se ainda ninguém pegou esse item num /validar
                // antes (ou se quem pegou foi a própria pessoa perguntando
                // de novo) — Vitor/Matheus, 2026-09-10: "se usar o /validar
                // no mesmo pedido, não pode aparecer de novo para outros
                // usuários". Cada item fica "reservado" pra quem primeiro
                // rodou /validar nele, até alguém decidir (Aprovar/
                // Retrabalho/Reprovado apaga a linha em
                // qualidade_mensagens_enviadas via sincronizarMensagens).
                $tentativaAtual = ((int) $it['qualidade_tentativas']) + 1;
                $stmtDonoAtual->execute([':tabela' => $it['tabela_origem'], ':id' => $it['item_id'], ':tentativa' => $tentativaAtual]);
                $donoAtual = $stmtDonoAtual->fetchColumn();

                if ($donoAtual !== false && (string) $donoAtual !== (string) $chatIdMsg) {
                    $jaComOutro++;
                    continue;
                }

                notificarQualidade($db, $it['tabela_origem'], $it['item_id'], (string) $chatIdMsg);
                $reenviados++;
            } else {
                telegramApiCall($token, 'sendMessage', [
                    'chat_id' => $chatIdMsg,
                    'text' => qualidadeItemFichaTexto($numeroVal, $it),
                    'parse_mode' => 'HTML',
                ]);
            }
        }
        if ($reenviados > 0) {
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => "🔄 Te mandei {$reenviados} pendência(s) do pedido " . htmlspecialchars($numeroVal) . ' pra você validar.',
            ]);
        }
        if ($jaComOutro > 0) {
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => "👀 {$jaComOutro} item(ns) do pedido " . htmlspecialchars($numeroVal) . ' já foi(ram) pego(s) por outra pessoa no /validar — aguardando ela decidir.',
            ]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    // /reprovados — itens em 🔧 Retrabalho (mesma etiqueta) ou ❌ Reprovado
    // (etiqueta nova, liderança + ERP já avisados) AGORA. Também informativo
    // — decidir já foi feito nos 3 botões da mensagem original.
    if ($textoMsg === '/reprovados') {
        if (!$estaAutorizado()) {
            $negarNaoAutorizado();
        }
        $itens = qualidadeItensReprovados($db);
        if (!$itens) {
            telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => '🎉 Nenhum item em retrabalho ou reprovado agora.']);
            echo json_encode(['ok' => true]);
            exit;
        }
        telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => '🔧 <b>Retrabalho/Reprovados aguardando retorno</b> (' . count($itens) . ')', 'parse_mode' => 'HTML']);
        foreach ($itens as $it) {
            $idExibicao = ($it['tabela_origem'] === 'itens_os') ? 'OS' . $it['item_id'] : (string) $it['item_id'];
            $emojiBucket = $it['bucket'] === 'Retrabalho' ? '🔧' : '❌';
            $linhas = [
                "$emojiBucket <b>" . htmlspecialchars($it['bucket']) . "</b> — tentativa {$it['qualidade_tentativas']}",
                'Pedido: <b>' . htmlspecialchars((string) $it['numero_pedido']) . '</b>',
                'Equipamento: ' . htmlspecialchars($it['equipamento'] ?? '-'),
                'Peça: ' . htmlspecialchars((string) ($it['posicao_no_pedido'] ?? '-')),
                'Cor: ' . htmlspecialchars($it['cor'] ?: 'Não informada'),
                "ID: {$idExibicao}",
            ];
            if ($it['reprovado_por']) {
                $quando = $it['reprovado_em'] ? date('d/m H:i', strtotime($it['reprovado_em'])) : '';
                $linhas[] = htmlspecialchars($it['bucket']) . ' por @' . htmlspecialchars($it['reprovado_por']) . ($quando ? " em $quando" : '');
            }
            if (!empty($it['motivo'])) {
                $linhas[] = 'Motivo: ' . htmlspecialchars($it['motivo']);
            }
            // Reimpressão e notificação QM só são coisa do bucket Reprovado
            // — Retrabalho nunca pede etiqueta nova nem abre QM.
            if ($it['bucket'] === 'Reprovado') {
                if (!empty($it['qm_code'])) {
                    $linhas[] = 'Notificação QM: ' . htmlspecialchars($it['qm_code']);
                }
                $linhas[] = $it['reimpressao_liberada'] ? '🖨️ Reimpressão já liberada.' : '🖨️ Aguardando liderança liberar a reimpressão.';
            }
            telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => implode("\n", $linhas), 'parse_mode' => 'HTML']);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    // /inspetor <nome> — histórico de decisões de uma pessoa específica (o
    // ranking agregado não existe aqui — isso já entra direto no detalhe).
    if (preg_match('/^\/inspetor(?:\s+(.+))?$/iu', $textoMsg, $mIns)) {
        if (!$estaAutorizado()) {
            $negarNaoAutorizado();
        }
        $nomeIns = trim($mIns[1] ?? '');
        if ($nomeIns === '') {
            telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => 'Manda assim: /inspetor NOME (ou parte do nome) — ex.: /inspetor Natália']);
            echo json_encode(['ok' => true]);
            exit;
        }
        $hist = qualidadeHistoricoInspetor($db, $nomeIns);
        if (!$hist) {
            telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => 'Nenhuma validação encontrada pra "' . htmlspecialchars($nomeIns) . '".']);
            echo json_encode(['ok' => true]);
            exit;
        }
        $linhas = ['👤 <b>Histórico de ' . htmlspecialchars($nomeIns) . '</b> (' . count($hist) . ' mais recente(s))'];
        foreach ($hist as $h) {
            $emoji = $h['decisao'] === 'Aprovado' ? '✅' : ($h['decisao'] === 'Retrabalho' ? '🔧' : '❌');
            $quando = date('d/m H:i', strtotime($h['criado_em']));
            $linhas[] = "$emoji Pedido " . htmlspecialchars((string) $h['numero_pedido']) . ' — ' . htmlspecialchars($h['equipamento'] ?? '-')
                . ($h['cor'] ? ' (cor ' . htmlspecialchars($h['cor']) . ')' : '') . " — $quando";
        }
        telegramApiCall($token, 'sendMessage', ['chat_id' => $chatIdMsg, 'text' => implode("\n", $linhas), 'parse_mode' => 'HTML']);
        echo json_encode(['ok' => true]);
        exit;
    }

    // /ajuda (ou /help) — guia de comandos, pra não depender de decorar (Vitor,
    // 2026-09-08, mesmo pedido já atendido no bot do ERP: "coloque o comando
    // /ajuda para guiar o usuário").
    if ($textoMsg === '/ajuda' || $textoMsg === '/help') {
        telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatIdMsg,
            'text' => "🤖 <b>Comandos disponíveis</b>\n"
                . "/pendentes — pedidos aguardando validação agora, do mais antigo pro mais novo\n"
                . "/validar NÚMERO — busca um pedido direto: reenvia as pendências (com os 3 botões de decisão) e mostra o que já foi decidido\n"
                . "/reprovados — itens em Retrabalho ou Reprovados aguardando voltar pra produção/reimpressão\n"
                . "/inspetor NOME — histórico de decisões de uma pessoa\n"
                . "/status ID (ou OS+ID) qualidade — força um item pro gate de qualidade na hora\n"
                . "/meuid — mostra seu chat_id (pra ser cadastrado por quem cuida da lista)\n\n"
                . '💡 As inspeções chegam sozinhas, com 3 botões — ✅ Aprovar, 🔧 Retrabalho (mesma etiqueta) ou ❌ Reprovado (etiqueta nova, aciona liderança e ERP). Retrabalho e Reprovado pedem o motivo numa mensagem de texto antes de aplicar.',
            'parse_mode' => 'HTML',
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // /meuid — mesma utilidade do "qualquer outra mensagem" de antes, só que
    // como comando dedicado (o fallback abaixo continua cobrindo /start etc.).
    if ($textoMsg === '/meuid') {
        telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatIdMsg,
            'text' => "🆔 Seu chat_id é: {$chatIdMsg}\nPassa esse número pra ser cadastrado na lista de quem recebe as inspeções de qualidade.",
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Comando de "/" não reconhecido — antes caía na mensagem de "seu chat_id
    // é", confuso pra quem digitou um comando errado achando que ele existia;
    // agora aponta pro /ajuda. EXCETO "/start": esse é o primeiro contato de
    // gente nova (abriu o bot agora), continua caindo no fallback de baixo
    // (mostra o chat_id pra pedir cadastro) — não faz sentido mandar "não
    // reconheço" pra quem tá literalmente começando.
    if ($textoMsg !== '' && $textoMsg[0] === '/' && $textoMsg !== '/start') {
        telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatIdMsg,
            'text' => '🤔 Não reconheço esse comando. Manda /ajuda pra ver a lista completa.',
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Qualquer outra mensagem (ex: /start, ou texto solto) — só devolve o
    // chat_id pra facilitar o cadastro em qualidade_telegram_chats.
    telegramApiCall($token, 'sendMessage', [
        'chat_id' => $chatIdMsg,
        'text' => "👋 Seu chat_id é: {$chatIdMsg}\nPassa esse número pra ser cadastrado na lista de quem recebe as inspeções de qualidade.",
    ]);
    echo json_encode(['ok' => true]);
    exit;
}

// Sempre responde 200 pro Telegram não ficar reenviando o update, mesmo
// quando o callback_data é inesperado ou algo falha — os erros ficam só no log.
if (!$callback || empty($callback['data'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$callbackId = $callback['id'];
$chatId = $callback['message']['chat']['id'] ?? null;
$messageId = $callback['message']['message_id'] ?? null;
$usuarioTelegram = $callback['from']['username'] ?? ($callback['from']['first_name'] ?? 'desconhecido');

$partes = explode(':', $callback['data']);
// formato: q:<acao>:<p|o>:<id>:<tentativa>
if (count($partes) !== 5 || $partes[0] !== 'q') {
    echo json_encode(['ok' => true]);
    exit;
}
[, $acao, $tabelaCurta, $idStr, $tentativaStr] = $partes;
$id = (int) $idStr;
$tabela = ($tabelaCurta === 'o') ? 'itens_os' : 'itens_producao';

function responderCallback(string $token, string $callbackId, string $texto, bool $alerta = false): void
{
    telegramApiCall($token, 'answerCallbackQuery', [
        'callback_query_id' => $callbackId,
        'text' => $texto,
        'show_alert' => $alerta,
    ]);
}

function editarMensagem(string $token, $chatId, $messageId, string $texto): void
{
    if (!$chatId || !$messageId) {
        return;
    }
    telegramApiCall($token, 'editMessageText', [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $texto,
        'parse_mode' => 'HTML',
    ]);
}

// Edita TODAS as cópias da mensagem de inspeção (uma por chat de qualidade),
// não só a de quem clicou — senão as outras pessoas continuam vendo os
// botões ativos mesmo depois de alguém já ter decidido.
function sincronizarMensagens(PDO $db, string $token, string $tabela, int $id, int $tentativa, string $texto): void
{
    $stmt = $db->prepare(
        "SELECT chat_id, message_id FROM qualidade_mensagens_enviadas
         WHERE tabela_origem = :tabela AND item_id = :id AND tentativa = :tentativa"
    );
    $stmt->execute([':tabela' => $tabela, ':id' => $id, ':tentativa' => $tentativa]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $msg) {
        editarMensagem($token, $msg['chat_id'], $msg['message_id'], $texto);
    }

    $db->prepare(
        "DELETE FROM qualidade_mensagens_enviadas WHERE tabela_origem = :tabela AND item_id = :id AND tentativa = :tentativa"
    )->execute([':tabela' => $tabela, ':id' => $id, ':tentativa' => $tentativa]);
}

/**
 * Aplica de fato um Retrabalho ou Reprovado — chamada só DEPOIS que o motivo
 * chega por texto (fluxo qualidade_aguardando_motivo), nunca direto do
 * clique do botão (Vitor, 2026-09-09: "quando recusado deve ter o motivo
 * nas duas tentativas"). $bucket é 'Retrabalho' ou 'Reprovado' — decidido
 * pelo INSPETOR na hora do clique (Vitor, 2026-09-09: "crie a estrutura de
 * 3 botes de validação Aprovado, Retrabalho, Reprovado" — "Retrabalho é o
 * que tínhamos de 1 tratativa, o reprovado é a 2 tratativa"), não mais
 * inferido pela contagem de tentativa. Só o bucket Reprovado aciona a
 * liderança pra reimpressão E avisa o bot do ERP (Vitor, 2026-09-09: "além
 * de imprimir a etiqueta deve se abrir a notificação de qualidade do ERP
 * dentro do telegram" — grava em qualidade_reprovacoes_erp; quem lê e manda
 * de verdade é o cron do ERP, telegram_cron.php); Retrabalho só volta pra
 * produção com a mesma etiqueta, sem acionar mais nada.
 */
function aplicarDecisaoNegativa(PDO $db, string $token, string $tabela, int $id, int $tentativa, string $usuarioTelegram, string $chatId, string $motivo, string $bucket): void
{
    $stmt = $db->prepare("UPDATE $tabela
                           SET status = 'Pendente', status_qualidade = 'Reprovado',
                               qualidade_tentativas = qualidade_tentativas + 1, reimpressao_liberada = 0
                           WHERE id = :id AND status_qualidade = 'Aguardando'");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatId,
            'text' => 'Essa inspeção já tinha sido decidida antes do motivo chegar — nada foi alterado.',
        ]);
        return;
    }

    $db->prepare("INSERT INTO qualidade_inspecoes (tabela_origem, item_id, tentativa, decisao, telegram_user, telegram_chat_id, motivo)
                   VALUES (:tabela, :id, :tentativa, :decisao, :usuario, :chat_id, :motivo)")
       ->execute([
           ':tabela' => $tabela, ':id' => $id, ':tentativa' => $tentativa, ':decisao' => $bucket,
           ':usuario' => $usuarioTelegram, ':chat_id' => $chatId, ':motivo' => $motivo,
       ]);

    $stmtItem = $db->prepare("SELECT numero_pedido, equipamento, cor, qualidade_tentativas FROM $tabela WHERE id = ?");
    $stmtItem->execute([$id]);
    $item = $stmtItem->fetch(PDO::FETCH_ASSOC) ?: [];
    $novaTentativa = (int) ($item['qualidade_tentativas'] ?? $tentativa);
    $idExibicao = ($tabela === 'itens_os') ? 'OS' . $id : (string) $id;
    $motivoEsc = htmlspecialchars($motivo);
    $emojiBucket = $bucket === 'Retrabalho' ? '🔧' : '❌';

    if ($bucket === 'Reprovado') {
        $textoDecisao = "$emojiBucket <b>Reprovado</b> por @{$usuarioTelegram}.\nMotivo: {$motivoEsc}\nItem voltou para produção — tentativa {$novaTentativa}, nova etiqueta necessária.";
    } else {
        $textoDecisao = "$emojiBucket <b>Retrabalho</b> por @{$usuarioTelegram}.\nMotivo: {$motivoEsc}\nItem voltou para produção — reproduzir com a mesma etiqueta.";
    }
    sincronizarMensagens($db, $token, $tabela, $id, $tentativa, $textoDecisao);
    telegramApiCall($token, 'sendMessage', ['chat_id' => $chatId, 'text' => "$emojiBucket $bucket — motivo registrado."]);

    // Só o bucket Reprovado precisa de etiqueta nova — Retrabalho nunca
    // aciona liderança nem ERP, não importa em qual tentativa esteja.
    if ($bucket === 'Reprovado') {
        // Reimpressão: pede aprovação de quem é liderança OU ganhou a
        // permissão avulsa 'qualidade.aprovar_reimpressao' pela tela nova do
        // ERP (Vitor, 2026-09-09) — ex.: um inspetor de qualidade que não é
        // liderança, mas foi liberado especificamente pra essa ação.
        $stmtLiderancas = $db->prepare("SELECT chat_id FROM qualidade_telegram_chats WHERE ativo = 1 AND (tipo = 'lideranca' OR pode_aprovar_reimpressao = 1)");
        $stmtLiderancas->execute();
        $chatsLiderancas = $stmtLiderancas->fetchAll(PDO::FETCH_COLUMN);

        if (empty($chatsLiderancas)) {
            error_log('aplicarDecisaoNegativa: nenhuma liderança cadastrada/ativa pra aprovar reimpressão.');
        }

        $tabelaCurta = ($tabela === 'itens_os') ? 'o' : 'p';
        $tecladoImprimir = [
            'inline_keyboard' => [[
                ['text' => '🖨️ Aprovar impressão', 'callback_data' => "q:imprimir:{$tabelaCurta}:{$id}:{$novaTentativa}"],
            ]],
        ];
        $stmtRegistrarMsgLideranca = $db->prepare(
            "INSERT INTO qualidade_mensagens_enviadas (tabela_origem, item_id, tentativa, chat_id, message_id)
             VALUES (:tabela, :id, :tentativa, :chat_id, :message_id)"
        );
        foreach ($chatsLiderancas as $chatIdLideranca) {
            $respostaLideranca = telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdLideranca,
                'text' => "🖨️ Item {$idExibicao} reprovado (tentativa {$novaTentativa}) pela qualidade — confirma a impressão da nova etiqueta (-PQ/-EQ)?\nMotivo: {$motivoEsc}",
                'reply_markup' => $tecladoImprimir,
            ]);
            if (!empty($respostaLideranca['ok'])) {
                $stmtRegistrarMsgLideranca->execute([
                    ':tabela' => $tabela,
                    ':id' => $id,
                    ':tentativa' => $novaTentativa,
                    ':chat_id' => $chatIdLideranca,
                    ':message_id' => $respostaLideranca['result']['message_id'] ?? 0,
                ]);
            }
        }

        // Além da liderança local, avisa o bot do ERP também — grava aqui,
        // o cron do ERP (telegram_cron.php) lê e manda pra quem tem acesso
        // ao módulo Qualidade lá dentro do próprio Telegram.
        $db->prepare(
            "INSERT INTO qualidade_reprovacoes_erp (tabela_origem, item_id, numero_pedido, equipamento, cor, tentativa, motivo, decidido_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([$tabela, $id, $item['numero_pedido'] ?? '', $item['equipamento'] ?? null, $item['cor'] ?? null, $novaTentativa, $motivo, $usuarioTelegram]);
    }
}

try {
    if ($acao === 'aprovar') {
        $stmt = $db->prepare("UPDATE $tabela SET status_qualidade = 'Aprovado' WHERE id = :id AND status_qualidade = 'Aguardando'");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            responderCallback($token, $callbackId, 'Esta inspeção já foi decidida por outra pessoa.', true);
            echo json_encode(['ok' => true]);
            exit;
        }

        $db->prepare("INSERT INTO qualidade_inspecoes (tabela_origem, item_id, tentativa, decisao, telegram_user, telegram_chat_id)
                       VALUES (:tabela, :id, :tentativa, 'Aprovado', :usuario, :chat_id)")
           ->execute([
               ':tabela' => $tabela,
               ':id' => $id,
               ':tentativa' => (int) $tentativaStr,
               ':usuario' => $usuarioTelegram,
               ':chat_id' => (string) $chatId,
           ]);

        $textoDecisao = "✅ <b>Aprovado</b> por @{$usuarioTelegram}.\nItem liberado para embalagem.";

        // Ressalva (Vitor, 2026-09-09: "se aprovado depois da primeira
        // tentativa deve aparecer aprovado com a ressalva que sera o
        // motivo") — se essa NÃO é a 1ª tentativa, busca a decisão negativa
        // anterior (Retrabalho OU Reprovado, tentativa - 1) e mostra o
        // motivo dela junto do Aprovado.
        $tentativaAtual = (int) $tentativaStr;
        if ($tentativaAtual > 1) {
            $stmtMotivoAnterior = $db->prepare(
                "SELECT decisao, motivo FROM qualidade_inspecoes WHERE tabela_origem = ? AND item_id = ? AND tentativa = ? AND decisao IN ('Retrabalho', 'Reprovado') ORDER BY id DESC LIMIT 1"
            );
            $stmtMotivoAnterior->execute([$tabela, $id, $tentativaAtual - 1]);
            $anterior = $stmtMotivoAnterior->fetch(PDO::FETCH_ASSOC);
            if ($anterior && $anterior['motivo']) {
                $textoDecisao .= "\n\n⚠️ <i>Ressalva: essa peça já tinha sido " . htmlspecialchars(mb_strtolower($anterior['decisao'])) . " antes.</i>\nMotivo anterior: " . htmlspecialchars($anterior['motivo']);
            }
        }

        sincronizarMensagens($db, $token, $tabela, $id, $tentativaAtual, $textoDecisao);
        responderCallback($token, $callbackId, 'Aprovado.');
    } elseif ($acao === 'retrabalho' || $acao === 'reprovar') {
        // NÃO aplica direto — primeiro confere se ainda dá pra decidir, e
        // pede o motivo por texto (Vitor, 2026-09-09: "quando recusado deve
        // ter o motivo nas duas tentativas"; "crie a estrutura de 3 botes de
        // validação Aprovado, Retrabalho, Reprovado" — o bucket já vem do
        // BOTÃO que a pessoa apertou, não é mais inferido pela tentativa).
        // aplicarDecisaoNegativa() (acima) só roda depois que o motivo
        // chegar, lá em cima no bloco de mensagem.
        $bucket = ($acao === 'retrabalho') ? 'Retrabalho' : 'Reprovado';

        $stmtCheck = $db->prepare("SELECT status_qualidade FROM $tabela WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() !== 'Aguardando') {
            responderCallback($token, $callbackId, 'Esta inspeção já foi decidida por outra pessoa.', true);
            echo json_encode(['ok' => true]);
            exit;
        }

        $db->prepare(
            "INSERT INTO qualidade_aguardando_motivo (chat_id, tabela_origem, item_id, tentativa, bucket, message_id) VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE tabela_origem = VALUES(tabela_origem), item_id = VALUES(item_id), tentativa = VALUES(tentativa), bucket = VALUES(bucket), message_id = VALUES(message_id), criado_em = NOW()"
        )->execute([(string) $chatId, $tabela, $id, (int) $tentativaStr, $bucket, $messageId]);

        responderCallback($token, $callbackId, "✍️ Manda o motivo do $bucket numa mensagem de texto agora.", true);
    } elseif ($acao === 'imprimir') {
        $stmt = $db->prepare("UPDATE $tabela SET reimpressao_liberada = 1 WHERE id = :id AND status_qualidade = 'Reprovado'");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            responderCallback($token, $callbackId, 'Esta impressão já foi liberada ou o item mudou de estado.', true);
            echo json_encode(['ok' => true]);
            exit;
        }

        sincronizarMensagens($db, $token, $tabela, $id, (int) $tentativaStr, "🖨️ Impressão liberada por @{$usuarioTelegram}.\nA etiqueta entra na próxima rodada do CRON de impressão.");
        responderCallback($token, $callbackId, 'Impressão liberada.');
    } else {
        responderCallback($token, $callbackId, 'Ação desconhecida.');
    }
} catch (Throwable $e) {
    error_log('telegram_qualidade_webhook: ' . $e->getMessage());
    responderCallback($token, $callbackId, 'Erro ao processar. Tente novamente.', true);
}

echo json_encode(['ok' => true]);
