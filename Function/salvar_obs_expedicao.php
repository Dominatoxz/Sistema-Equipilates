<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

if (!validarTokenCSRF($dados['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada ou inválida. Recarregue a página e tente de novo.']);
    exit;
}

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

    // observacoes_expedicao.numero_pedido é NOT NULL sem default — buscamos
    // direto de pedidos_prontos por id_pedido (mesmo ajuste feito em
    // salvar_obs_financeiro.php, que pegou esse erro primeiro).
    $stmtNumero = $db->prepare("SELECT numero_pedido FROM pedidos_prontos WHERE id = :id_pedido");
    $stmtNumero->bindParam(':id_pedido', $id_pedido);
    $stmtNumero->execute();
    $numero_pedido = $stmtNumero->fetchColumn();

    if (!$numero_pedido) {
        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado para o id informado.']);
        exit;
    }

    $stmtIns = $db->prepare("INSERT INTO observacoes_expedicao (id_pedido, numero_pedido, observacao) VALUES (:id_pedido, :numero_pedido, :observacao)");
    $stmtIns->bindParam(':id_pedido', $id_pedido);
    $stmtIns->bindParam(':numero_pedido', $numero_pedido);
    $stmtIns->bindParam(':observacao', $observacao);
    $stmtIns->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
