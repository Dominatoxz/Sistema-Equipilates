<?php
// Script de uso único, pra criar/remover um item fictício e testar a
// impressão automática de ponta a ponta. Remover do servidor depois de usar.

require_once '../global.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';

if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$db = (new Database())->getConnection();
$acao = $_GET['acao'] ?? 'inserir';

if ($acao === 'debug') {
    $stmt = $db->prepare("SELECT * FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO'");
    $stmt->execute();
    echo json_encode(['success' => true, 'item' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    exit;
}

if ($acao === 'debug2') {
    $inicioSemana = new DateTime('next monday');
    $fimSemana = (clone $inicioSemana)->modify('+6 days');
    $paramDataIni = $inicioSemana->format('Y-m-d');
    $paramDataFim = $fimSemana->format('Y-m-d');

    $sql = "
        SELECT t.id, t.numero_pedido, t.equipamento, t.status, t.status_qualidade, t.prazo_producao,
               STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') AS data_convertida,
               (STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') BETWEEN :data_ini1 AND :data_fim1) AS dentro_janela
        FROM itens_producao t
        WHERE t.numero_pedido = 'TESTE-AUTO'
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':data_ini1' => $paramDataIni, ':data_fim1' => $paramDataFim]);
    echo json_encode(['success' => true, 'janela_ini' => $paramDataIni, 'janela_fim' => $paramDataFim, 'linha' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    exit;
}

if ($acao === 'debug3') {
    $inicioSemana = new DateTime('next monday');
    $fimSemana = (clone $inicioSemana)->modify('+6 days');
    $paramDataIni = $inicioSemana->format('Y-m-d');
    $paramDataFim = $fimSemana->format('Y-m-d');

    $sql = "
        SELECT t.id, t.numero_pedido, t.equipamento, t.posicao_no_pedido, t.cor, t.prazo_producao, t.qualidade_tentativas, t.reimpressao_liberada, t.status_qualidade, 'PRODUCAO' AS tabela_origem,
               qi.qm_code AS qm_code, qi.motivo AS qm_motivo, qi.telegram_user AS qm_inspetor, qi.criado_em AS qm_decidido_em
        FROM itens_producao t
        LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = 'itens_producao' AND qi.item_id = t.id
          AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = 'itens_producao' AND item_id = t.id)
        WHERE t.status != 'Embalado' AND t.equipamento NOT LIKE 'Emb.%'
          AND (
                (t.status_qualidade = 'Reprovado' AND t.reimpressao_liberada = 1)
                OR (t.status_qualidade != 'Reprovado' AND STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') BETWEEN :data_ini1 AND :data_fim1)
              )
        UNION ALL
        SELECT t.id, t.numero_pedido, t.equipamento, t.posicao_no_pedido, t.cor, t.prazo_producao, t.qualidade_tentativas, t.reimpressao_liberada, t.status_qualidade, 'OS' AS tabela_origem,
               qi.qm_code AS qm_code, qi.motivo AS qm_motivo, qi.telegram_user AS qm_inspetor, qi.criado_em AS qm_decidido_em
        FROM itens_os t
        LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = 'itens_os' AND qi.item_id = t.id
          AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = 'itens_os' AND item_id = t.id)
        WHERE t.status != 'Embalado' AND t.equipamento NOT LIKE 'Emb.%'
          AND (
                (t.status_qualidade = 'Reprovado' AND t.reimpressao_liberada = 1)
                OR (t.status_qualidade != 'Reprovado' AND STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') BETWEEN :data_ini2 AND :data_fim2)
              )
        ORDER BY equipamento ASC, STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC, id ASC
    ";
    try {
        $stmtItens = $db->prepare($sql);
        $stmtItens->execute([
            ':data_ini1' => $paramDataIni,
            ':data_fim1' => $paramDataFim,
            ':data_ini2' => $paramDataIni,
            ':data_fim2' => $paramDataFim,
        ]);
        $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
        $achouTeste = array_values(array_filter($itens, fn($i) => $i['numero_pedido'] === 'TESTE-AUTO'));
        echo json_encode(['success' => true, 'total_itens' => count($itens), 'teste_encontrado' => $achouTeste]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($acao === 'limparopcache') {
    if (function_exists('opcache_reset')) {
        $ok = opcache_reset();
        echo json_encode(['success' => true, 'opcache_reset' => $ok]);
    } else {
        echo json_encode(['success' => false, 'error' => 'opcache_reset indisponivel']);
    }
    exit;
}

if ($acao === 'debug4') {
    $stmt = $db->prepare("SELECT * FROM impressoes_etiquetas WHERE id_item IN (105954, 105952, 86469) ORDER BY id_item, tipo_etiqueta");
    $stmt->execute();
    echo json_encode(['success' => true, 'registros' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($acao === 'debug5') {
    $stmtCols = $db->query("SHOW COLUMNS FROM qualidade_inspecoes");
    $colunas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
    $stmtRow = $db->prepare("SELECT * FROM qualidade_inspecoes WHERE item_id = 105952");
    $stmtRow->execute();
    echo json_encode(['success' => true, 'colunas' => $colunas, 'linha' => $stmtRow->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($acao === 'inserirqm') {
    $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'")->execute();

    $db->prepare("INSERT INTO itens_producao
        (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status, status_qualidade, qualidade_tentativas, reimpressao_liberada)
        VALUES ('TESTE-AUTO-QM', '16/09/2026', 'TESTE QM ETIQUETA 2', 1, 'TESTE', 'Pendente', 'Reprovado', 1, 1)")
       ->execute();
    $idNovo = (int) $db->lastInsertId();

    $db->prepare("INSERT INTO qualidade_inspecoes (tabela_origem, item_id, tentativa, decisao, motivo, telegram_user, telegram_chat_id, qm_code)
        VALUES ('itens_producao', :item_id, 1, 'Reprovado', 'Teste de etiqueta de identificacao (Claude)', 'ClaudeTeste', 'teste-script', :qm_code)")
       ->execute([':item_id' => $idNovo, ':qm_code' => 'QM-TESTE-CLAUDE-' . $idNovo]);

    echo json_encode(['success' => true, 'id' => $idNovo]);
    exit;
}

if ($acao === 'remover') {
    $stmtBuscaQm = $db->prepare("SELECT id FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'");
    $stmtBuscaQm->execute();
    $idQm = $stmtBuscaQm->fetchColumn();
    if ($idQm) {
        $db->prepare("DELETE FROM qualidade_inspecoes WHERE tabela_origem = 'itens_producao' AND item_id = :id")->execute([':id' => $idQm]);
    }
    $stmt = $db->prepare("DELETE FROM itens_producao WHERE numero_pedido IN ('TESTE-AUTO', 'TESTE-AUTO-QM')");
    $stmt->execute();
    echo json_encode(['success' => true, 'removidos' => $stmt->rowCount()]);
    exit;
}

$stmt = $db->prepare("INSERT INTO itens_producao
    (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status)
    VALUES ('TESTE-AUTO', '16/09/2026', 'ITEM TESTE AUTOMACAO', 1, 'AZUL TESTE', 'Pendente')
    ON DUPLICATE KEY UPDATE status = 'Pendente'");
$stmt->execute();

echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
