<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/telegram_api.php';
require_once __DIR__ . '/notificar_qualidade.php';

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

// Mensagem de texto comum (/start, /status ...) — não é decisão de qualidade
// em si, é comando de quem já está cadastrado.
if (!$callback && isset($update['message']['chat']['id'])) {
    $chatIdMsg = $update['message']['chat']['id'];
    $textoMsg = trim($update['message']['text'] ?? '');

    // /status <id> qualidade  ou  /status OS<id> qualidade — coloca o item
    // em "Aguardando qualidade" manualmente e dispara a mensagem de
    // aprovar/reprovar, sem precisar esperar a bipagem física. Só quem já
    // está cadastrado (qualidade ou liderança) pode usar.
    if (preg_match('/^\/status\s+(OS)?(\d+)\s+qualidade$/i', $textoMsg, $m)) {
        $stmtAutorizado = $db->prepare('SELECT 1 FROM qualidade_telegram_chats WHERE chat_id = ? AND ativo = 1');
        $stmtAutorizado->execute([(string) $chatIdMsg]);

        if (!$stmtAutorizado->fetchColumn()) {
            telegramApiCall($token, 'sendMessage', [
                'chat_id' => $chatIdMsg,
                'text' => 'Esse comando é só pra quem já está cadastrado em qualidade_telegram_chats.',
            ]);
            echo json_encode(['ok' => true]);
            exit;
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

    // Qualquer outra mensagem (ex: /start) — só devolve o chat_id pra
    // facilitar o cadastro em qualidade_telegram_chats.
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

        sincronizarMensagens($db, $token, $tabela, $id, (int) $tentativaStr, "✅ <b>Aprovado</b> por @{$usuarioTelegram}.\nItem liberado para embalagem.");
        responderCallback($token, $callbackId, 'Aprovado.');
    } elseif ($acao === 'reprovar') {
        $stmt = $db->prepare("UPDATE $tabela
                               SET status = 'Pendente', status_qualidade = 'Reprovado',
                                   qualidade_tentativas = qualidade_tentativas + 1, reimpressao_liberada = 0
                               WHERE id = :id AND status_qualidade = 'Aguardando'");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            responderCallback($token, $callbackId, 'Esta inspeção já foi decidida por outra pessoa.', true);
            echo json_encode(['ok' => true]);
            exit;
        }

        $db->prepare("INSERT INTO qualidade_inspecoes (tabela_origem, item_id, tentativa, decisao, telegram_user, telegram_chat_id)
                       VALUES (:tabela, :id, :tentativa, 'Reprovado', :usuario, :chat_id)")
           ->execute([
               ':tabela' => $tabela,
               ':id' => $id,
               ':tentativa' => (int) $tentativaStr,
               ':usuario' => $usuarioTelegram,
               ':chat_id' => (string) $chatId,
           ]);

        $stmtTentativas = $db->prepare("SELECT qualidade_tentativas FROM $tabela WHERE id = ?");
        $stmtTentativas->execute([$id]);
        $novaTentativa = (int) $stmtTentativas->fetchColumn();

        $idExibicao = ($tabela === 'itens_os') ? 'OS' . $id : (string) $id;

        // Só a partir da 2ª reprovação seguida é que pede etiqueta nova —
        // na 1ª, o item só volta pra produção com a mesma etiqueta física
        // de antes (só muda o símbolo ⚠️ na tela).
        if ($novaTentativa >= 2) {
            $textoDecisao = "❌ <b>Reprovado</b> por @{$usuarioTelegram}.\nItem voltou para produção — tentativa {$novaTentativa}, nova etiqueta necessária.";
        } else {
            $textoDecisao = "❌ <b>Reprovado</b> por @{$usuarioTelegram}.\nItem voltou para produção — reproduzir com a mesma etiqueta.";
        }
        sincronizarMensagens($db, $token, $tabela, $id, (int) $tentativaStr, $textoDecisao);
        responderCallback($token, $callbackId, 'Reprovado.');

        if ($novaTentativa >= 2) {
            // Segunda aprovação (liberar a reimpressão pro CRON) vai pra
            // liderança, não pra quem decidiu a inspeção de qualidade.
            $stmtLiderancas = $db->prepare("SELECT chat_id FROM qualidade_telegram_chats WHERE ativo = 1 AND tipo = 'lideranca'");
            $stmtLiderancas->execute();
            $chatsLiderancas = $stmtLiderancas->fetchAll(PDO::FETCH_COLUMN);

            if (empty($chatsLiderancas)) {
                error_log('telegram_qualidade_webhook: nenhuma liderança cadastrada/ativa pra aprovar reimpressão.');
            }

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
                    'text' => "🖨️ Item {$idExibicao} reprovado {$novaTentativa}x pela qualidade — confirma a impressão da nova etiqueta (-PQ/-EQ)?",
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
        }
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
