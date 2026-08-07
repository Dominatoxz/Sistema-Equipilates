<?php
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
require_once 'trava.php';

header('Content-Type: application/json');

if (!isset($_SESSION['nivel_acesso']) || !in_array($_SESSION['nivel_acesso'], CARGOS_EXPEDICAO_ACAO)) {
    echo json_encode(['success' => false, 'error' => 'Seu cargo não tem permissão para esta ação.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método de requisição inválido.']);
    exit();
}

$jsonRecebido = file_get_contents('php://input');
$dados = json_decode($jsonRecebido, true);

if (!validarTokenCSRF($dados['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada ou inválida. Recarregue a página e tente de novo.']);
    exit();
}

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

    $sqlBusca = "SELECT numero_pedido, prazo_producao FROM pedidos_prontos WHERE id = ?";
    $stmtBusca = $db->prepare($sqlBusca);
    $stmtBusca->execute([$idPedido]);
    $dadosPedido = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$dadosPedido) {
        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado ou já expedido.']);
        exit;
    }

    $numeroPedidoReal = $dadosPedido['numero_pedido'];
    $prazoProducao = $dadosPedido['prazo_producao'];

    date_default_timezone_set('America/Sao_Paulo');
    $dataConclusaoPHP = date('Y-m-d H:i:s');

    $db->beginTransaction();

    $queryInsert = "INSERT INTO pedidos_expedidos (numero_pedido, prazo_producao, data_conclusao, status_posvenda)
                    VALUES (?, ?, ?, 'Finalizado')";
    $stmtInsert = $db->prepare($queryInsert);

    $stmtInsert->execute([
        $numeroPedidoReal,
        $prazoProducao,
        $dataConclusaoPHP
    ]);

    $queryUpdate = "UPDATE pedidos_prontos SET status_posvenda = 'Expedido' WHERE id = ?";
    $stmtUpdate = $db->prepare($queryUpdate);
    $stmtUpdate->execute([$idPedido]);
    $db->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Erro no Banco: ' . $e->getMessage()]);
}
