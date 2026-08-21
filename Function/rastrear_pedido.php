<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
require_once '../Function/cargos.php';

header('Content-Type: application/json');

if (!isset($_SESSION['nivel_acesso']) || !in_array($_SESSION['nivel_acesso'], CARGOS_RASTREAMENTO)) {
    echo json_encode(['success' => false, 'error' => 'Seu cargo não tem permissão para ver o rastreamento de pedidos.']);
    exit;
}

$numero = isset($_GET['numero']) ? trim($_GET['numero']) : '';

if ($numero === '') {
    echo json_encode(['success' => false, 'error' => 'Informe um número de pedido para buscar.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $sistema = new Sistema($db);

    $resultado = $sistema->rastrearPedidos($numero);

    echo json_encode(['success' => true, 'pedidos' => $resultado]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
