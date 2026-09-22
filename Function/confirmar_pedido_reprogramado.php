<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
require_once '../Function/cargos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['nivel_acesso']) || !in_array($_SESSION['nivel_acesso'], CARGOS_PEDIDOS_REPROGRAMADOS)) {
    echo json_encode(['success' => false, 'error' => 'Seu cargo não tem permissão para esta ação.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!validarTokenCSRF($dados['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada ou inválida. Recarregue a página e tente de novo.']);
    exit();
}

$id = isset($dados['id']) ? (int) $dados['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido.']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $sistema = new Sistema($db);

    $usuarioNome = $_SESSION['usuario_logado'] ?? null;

    if ($sistema->confirmarPedidoReprogramado($id, $usuarioNome)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Este pedido já não está mais pendente (outra pessoa já deve ter confirmado). Recarregue a página.']);
    }
} catch (\PDOException $e) {
    error_log('confirmar_pedido_reprogramado: ' . $e->getMessage() . ' | Linha: ' . $e->getLine());
    echo json_encode(['success' => false, 'error' => 'Erro ao processar a solicitação. Tente novamente.']);
}
exit();
