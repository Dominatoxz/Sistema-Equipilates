<?php
require_once '../config/Database.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

if (!isset($dados['id_pedido']) || !isset($dados['observacao'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
    exit;
}

$id_pedido  = (int)$dados['id_pedido'];
$observacao = trim($dados['observacao']);

if (empty($observacao)) {
    echo json_encode(['success' => true]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmtIns = $db->prepare("INSERT INTO observacoes_expedicao (id_pedido, observacao) VALUES (:id_pedido, :observacao)");
    $stmtIns->bindParam(':id_pedido', $id_pedido);
    $stmtIns->bindParam(':observacao', $observacao);
    $stmtIns->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
