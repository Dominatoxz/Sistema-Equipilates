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

if ($acao === 'remover') {
    $stmt = $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO'");
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
