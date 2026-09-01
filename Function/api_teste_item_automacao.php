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

if ($acao === 'remover') {
    $stmt = $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO'");
    $stmt->execute();
    echo json_encode(['success' => true, 'removidos' => $stmt->rowCount()]);
    exit;
}

$stmt = $db->prepare("INSERT INTO itens_producao
    (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status)
    VALUES ('TESTE-AUTO', '10/09/2026', 'ITEM TESTE AUTOMACAO', 1, 'AZUL TESTE', 'Pendente')
    ON DUPLICATE KEY UPDATE status = 'Pendente'");
$stmt->execute();

echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
