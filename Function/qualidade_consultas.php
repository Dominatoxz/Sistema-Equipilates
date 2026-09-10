<?php

/*
 * Consultas compartilhadas do gate de qualidade — usadas pelos comandos do
 * bot (telegram_qualidade_webhook.php: /pendentes, /validar, /reprovados,
 * /inspetor) e pelo resumo diário (qualidade_resumo_diario.php), pra nunca
 * divergir (Vitor, 2026-09-08: "coloque as funcionalidades que falei sobre
 * a qualidade" — idade do pendente, reprovados, histórico por inspetor,
 * resumo diário; mesmo pacote já construído no bot do ERP, agora nativo
 * aqui que dá pra mexer direto no código deste bot).
 *
 * RECONSTRUÍDO em 2026-09-09 depois que um deploy externo (git checkout de
 * outro commit, "Alterações Torre Wall unit, prancha de molas e contagem")
 * sobrescreveu Function/ inteira e apagou este arquivo — nunca tinha sido
 * commitado no repositório de verdade, só existia direto no servidor. As
 * tabelas/colunas no banco (motivo, decisao com Retrabalho,
 * qualidade_aguardando_motivo, qualidade_reprovacoes_erp) não foram
 * afetadas, só o código PHP.
 */

// A partir de quantos dias aguardando um pedido vira 🔴 nas listas.
const QUALIDADE_DIAS_ALERTA = 2;

/**
 * Pedidos com item(ns) aguardando validação, do MAIS ANTIGO pro mais novo.
 * "Antigo" = quando a mensagem de inspeção foi mandada (qualidade_mensagens_
 * enviadas.criado_em); sem mensagem registrada (caso raríssimo), cai pra
 * data_inicio do item. Retorna [['numero_pedido'=>, 'dias'=>, 'itens'=>[...]],...].
 */
function qualidadeListaAguardando(PDO $db): array
{
    $porPedido = [];
    foreach (['itens_producao', 'itens_os'] as $tabela) {
        $stmt = $db->prepare(
            "SELECT i.id, i.numero_pedido, i.equipamento, i.cor, i.posicao_no_pedido,
                    MIN(COALESCE(m.criado_em, i.data_inicio)) AS desde
             FROM $tabela i
             LEFT JOIN qualidade_mensagens_enviadas m ON m.item_id = i.id AND m.tabela_origem = :tabela
             WHERE i.status_qualidade = 'Aguardando'
             GROUP BY i.id, i.numero_pedido, i.equipamento, i.cor, i.posicao_no_pedido"
        );
        $stmt->execute([':tabela' => $tabela]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $np = $r['numero_pedido'];
            if (!isset($porPedido[$np])) {
                $porPedido[$np] = ['numero_pedido' => $np, 'desde' => $r['desde'], 'itens' => []];
            }
            if ($r['desde'] !== null && ($porPedido[$np]['desde'] === null || $r['desde'] < $porPedido[$np]['desde'])) {
                $porPedido[$np]['desde'] = $r['desde'];
            }
            $porPedido[$np]['itens'][] = [
                'tabela_origem' => $tabela,
                'item_id' => (int) $r['id'],
                'equipamento' => $r['equipamento'],
                'cor' => $r['cor'],
                'posicao_no_pedido' => $r['posicao_no_pedido'],
            ];
        }
    }

    $hoje = new DateTime();
    $lista = array_values($porPedido);
    foreach ($lista as &$p) {
        $p['dias'] = $p['desde'] !== null ? $hoje->diff(new DateTime($p['desde']))->days : -1;
    }
    unset($p);
    usort($lista, fn($a, $b) => $b['dias'] <=> $a['dias']); // mais antigo primeiro
    return $lista;
}

/**
 * Itens ATUALMENTE reprovados (status_qualidade='Reprovado') — aguardando
 * voltar pra produção/reimpressão, com quem decidiu, quando e o motivo
 * (última decisão registrada). O campo 'bucket' diz se foi 🔧 Retrabalho
 * (mesma etiqueta) ou ❌ Reprovado (etiqueta nova + liderança/ERP avisados)
 * — Vitor, 2026-09-09: "crie a estrutura de 3 botes de validação". Some da
 * lista assim que o item passar de novo pelo gate (nova tentativa aprovada).
 */
function qualidadeItensReprovados(PDO $db): array
{
    $itens = [];
    foreach (['itens_producao', 'itens_os'] as $tabela) {
        $stmt = $db->query(
            "SELECT t.id, t.numero_pedido, t.equipamento, t.cor, t.posicao_no_pedido, t.qualidade_tentativas, t.reimpressao_liberada,
                    qi.telegram_user AS reprovado_por, qi.criado_em AS reprovado_em, qi.motivo AS motivo, qi.decisao AS bucket, qi.qm_code AS qm_code
             FROM $tabela t
             LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = '$tabela' AND qi.item_id = t.id
               AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = '$tabela' AND item_id = t.id)
             WHERE t.status_qualidade = 'Reprovado'"
        );
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $itens[] = [
                'tabela_origem' => $tabela,
                'item_id' => (int) $r['id'],
                'numero_pedido' => $r['numero_pedido'],
                'equipamento' => $r['equipamento'],
                'cor' => $r['cor'],
                'posicao_no_pedido' => $r['posicao_no_pedido'],
                'qualidade_tentativas' => (int) $r['qualidade_tentativas'],
                'reimpressao_liberada' => (bool) $r['reimpressao_liberada'],
                'reprovado_por' => $r['reprovado_por'],
                'reprovado_em' => $r['reprovado_em'],
                'motivo' => $r['motivo'],
                'bucket' => $r['bucket'] ?: 'Reprovado',
                'qm_code' => $r['qm_code'],
            ];
        }
    }
    usort($itens, fn($a, $b) => strcmp($b['reprovado_em'] ?? '', $a['reprovado_em'] ?? ''));
    return $itens;
}

/**
 * Histórico de decisões de UM inspetor (telegram_user contém $nome, sem
 * diferenciar maiúsculas — LIKE), mais recente primeiro. Limitado a 30.
 */
function qualidadeHistoricoInspetor(PDO $db, string $nome): array
{
    $linhas = [];
    foreach (['itens_producao', 'itens_os'] as $tabela) {
        $stmt = $db->prepare(
            "SELECT qi.decisao, qi.criado_em, t.numero_pedido, t.equipamento, t.cor
             FROM qualidade_inspecoes qi
             JOIN $tabela t ON t.id = qi.item_id
             WHERE qi.tabela_origem = :tabela AND qi.telegram_user LIKE :nome
             ORDER BY qi.criado_em DESC LIMIT 30"
        );
        $stmt->execute([':tabela' => $tabela, ':nome' => '%' . $nome . '%']);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $linhas[] = $r;
        }
    }
    usort($linhas, fn($a, $b) => strcmp($b['criado_em'], $a['criado_em']));
    return array_slice($linhas, 0, 30);
}

/**
 * Ranking de inspetores só de UM DIA (Vitor, 2026-09-09: "no resumo do dia
 * da qualidade quero que coloque a informação dos inspetores, pra eu saber
 * quem validou mais e quem validou menos"). Parte de TODO MUNDO ativo
 * cadastrado em qualidade_telegram_chats — quem não decidiu nada nesse dia
 * aparece com 0 em vez de sumir da lista, que é justamente "quem validou
 * menos". Mesma normalização do chat_id ("tg:" tira o prefixo, ver
 * qualidade_decidir_item no lado do ERP) usada aqui também, caso a pessoa
 * tenha decidido pelo bot do ERP em vez deste.
 */
function qualidadeInspetoresRankingDia(PDO $db, string $dia): array
{
    $stmt = $db->prepare(
        "SELECT qtc.nome AS inspetor,
                COUNT(qi.id) AS total,
                SUM(qi.decisao = 'Aprovado') AS aprovados,
                SUM(qi.decisao IN ('Retrabalho', 'Reprovado')) AS reprovados
         FROM (SELECT chat_id, MIN(nome) AS nome FROM qualidade_telegram_chats WHERE ativo = 1 GROUP BY chat_id) qtc
         LEFT JOIN qualidade_inspecoes qi
           ON TRIM(LEADING 'tg:' FROM qi.telegram_chat_id) = qtc.chat_id AND DATE(qi.criado_em) = :dia
         GROUP BY qtc.chat_id, qtc.nome
         ORDER BY total DESC, inspetor ASC"
    );
    $stmt->execute([':dia' => $dia]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Texto "quem validou mais e quem validou menos hoje" — usado no resumo
 *  diário automático. Medalha só no 1º colocado com validação > 0; quem
 *  ficou em 0 aparece mesmo assim, sem destaque. */
function qualidadeRankingDiaTexto(array $rows): string
{
    if (!$rows) {
        return '';
    }
    $linhas = ['👤 <b>Por inspetor hoje</b>'];
    foreach ($rows as $i => $r) {
        $medalha = ($i === 0 && $r['total'] > 0) ? '🥇 ' : '• ';
        $detalhe = $r['total'] > 0 ? " (✅ {$r['aprovados']} · ❌ {$r['reprovados']})" : '';
        $linhas[] = $medalha . htmlspecialchars((string) $r['inspetor']) . ": <b>{$r['total']}</b>{$detalhe}";
    }
    return implode("\n", $linhas);
}

/**
 * TODOS os itens de um pedido que já passaram pelo gate (status_qualidade
 * <> 'N/A' — inclui Aguardando, Aprovado e Reprovado), com a ÚLTIMA decisão
 * de cada um. Usada pelo /validar NÚMERO (Vitor, 2026-09-08: "ideal é ter um
 * /validacao igual o do ERP" — buscar direto um pedido pelo número). Item
 * ainda Aguardando é resend de verdade (via notificarQualidade, ver
 * telegram_qualidade_webhook.php); item já decidido volta só informativo —
 * pra REABRIR de verdade uma decisão já tomada, o caminho existente
 * continua sendo /status ID qualidade (força status_qualidade='Aguardando'
 * de novo do jeito certo, incrementando tentativa/reimpressão como sempre;
 * um botão de "rever" direto aqui iria pular esse controle).
 */
function qualidadeItensDoPedido(PDO $db, string $numero): array
{
    $itens = [];
    foreach (['itens_producao', 'itens_os'] as $tabela) {
        $stmt = $db->prepare(
            "SELECT t.id, t.equipamento, t.cor, t.posicao_no_pedido, t.status_qualidade, t.qualidade_tentativas,
                    qi.decisao AS ultima_decisao, qi.telegram_user AS ultimo_decisor, qi.criado_em AS decidido_em, qi.motivo AS motivo, qi.qm_code AS qm_code
             FROM $tabela t
             LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = :tabela AND qi.item_id = t.id
               AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = :tabela2 AND item_id = t.id)
             WHERE t.numero_pedido = :numero AND t.status_qualidade <> 'N/A'
             ORDER BY t.equipamento"
        );
        $stmt->execute([':tabela' => $tabela, ':tabela2' => $tabela, ':numero' => $numero]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $itens[] = [
                'tabela_origem' => $tabela,
                'item_id' => (int) $r['id'],
                'equipamento' => $r['equipamento'],
                'cor' => $r['cor'],
                'posicao_no_pedido' => $r['posicao_no_pedido'],
                'status_qualidade' => $r['status_qualidade'],
                'qualidade_tentativas' => (int) $r['qualidade_tentativas'],
                'ultima_decisao' => $r['ultima_decisao'],
                'ultimo_decisor' => $r['ultimo_decisor'],
                'decidido_em' => $r['decidido_em'],
                'motivo' => $r['motivo'],
                'qm_code' => $r['qm_code'],
            ];
        }
    }
    return $itens;
}

/** Ficha "Pedido/Equipamento/Peça/Cor/ID" de 1 item, com uma linha de rodapé
 *  conforme o status — usada pelo /validar NÚMERO pros itens JÁ decididos
 *  (informativo, sem botão — ver qualidadeItensDoPedido acima). */
function qualidadeItemFichaTexto(string $numero, array $it): string
{
    $idExibicao = ($it['tabela_origem'] === 'itens_os') ? 'OS' . $it['item_id'] : (string) $it['item_id'];
    $linhas = [
        '🔍 <b>Inspeção de qualidade</b>',
        'Pedido: <b>' . htmlspecialchars($numero) . '</b>',
        'Equipamento: ' . htmlspecialchars($it['equipamento'] ?? '-'),
        'Peça: ' . htmlspecialchars((string) ($it['posicao_no_pedido'] ?? '-')),
        'Cor: ' . htmlspecialchars($it['cor'] ?: 'Não informada'),
        "ID: {$idExibicao}",
        '',
    ];
    if ($it['status_qualidade'] === 'Aprovado') {
        $quando = $it['decidido_em'] ? date('d/m H:i', strtotime($it['decidido_em'])) : '';
        $linhas[] = '✅ <b>Aprovado</b>' . ($it['ultimo_decisor'] ? ' por @' . htmlspecialchars($it['ultimo_decisor']) : '') . ($quando ? " em $quando" : '');
    } elseif ($it['status_qualidade'] === 'Reprovado') {
        // status_qualidade do item continua só Aprovado/Reprovado (não mudou
        // o ENUM da tabela principal) — o BUCKET de verdade (Retrabalho ou
        // Reprovado) vem da última decisão em qualidade_inspecoes.
        $bucket = $it['ultima_decisao'] ?: 'Reprovado';
        $emojiBucket = $bucket === 'Retrabalho' ? '🔧' : '❌';
        $quando = $it['decidido_em'] ? date('d/m H:i', strtotime($it['decidido_em'])) : '';
        $linhas[] = "$emojiBucket <b>" . htmlspecialchars($bucket) . '</b>' . ($it['ultimo_decisor'] ? ' por @' . htmlspecialchars($it['ultimo_decisor']) : '') . ($quando ? " em $quando" : '') . " — tentativa {$it['qualidade_tentativas']}";
        if (!empty($it['motivo'])) {
            $linhas[] = 'Motivo: ' . htmlspecialchars($it['motivo']);
        }
        if ($bucket === 'Reprovado' && !empty($it['qm_code'])) {
            $linhas[] = 'Notificação QM: ' . htmlspecialchars($it['qm_code']);
        }
        $linhas[] = '<i>Pra reabrir de verdade (reimprimir/voltar pra produção), use /status ' . $idExibicao . ' qualidade.</i>';
    }
    return implode("\n", $linhas);
}

/** "🔴7975 (5d), 8347 (1d), ..." — idade de cada pedido aguardando. */
function qualidadeListaAguardandoTexto(array $lista): string
{
    $partes = [];
    foreach ($lista as $p) {
        $dias = $p['dias'];
        $sufixo = $dias < 0 ? '' : ($dias === 0 ? ' (hoje)' : " ({$dias}d)");
        $partes[] = ($dias >= QUALIDADE_DIAS_ALERTA ? '🔴' : '') . htmlspecialchars((string) $p['numero_pedido']) . $sufixo;
    }
    return implode(', ', $partes);
}
