<?php
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
require_once 'trava.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido.']);
    exit();
}

$jsonRecebido = file_get_contents('php://input');
$dados = json_decode($jsonRecebido, true);

if (!isset($dados['id_pedido']) || empty($dados['id_pedido'])) {
    echo json_encode(['success' => false, 'error' => 'ID do pedido não foi informado.']);
    exit();
}

$idPedido = filter_var($dados['id_pedido'], FILTER_VALIDATE_INT);

if (!$idPedido) {
    echo json_encode(['success' => false, 'error' => 'ID de pedido inválido.']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("UPDATE pedidos_prontos SET status_posvenda = 'Pós-venda', data_conclusao = NOW() WHERE id = :id");
    $resultado = $stmt->execute(['id' => $idPedido]);

    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'O banco não sofreu alterações. O ID existe?']);
    }

} catch (\PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Erro no MySQL: ' . $e->getMessage() . ' | Linha: ' . $e->getLine()
    ]);
}
exit();
exit();