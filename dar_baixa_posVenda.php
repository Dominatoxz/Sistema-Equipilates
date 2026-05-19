<?php
require_once './Model/Sistema.php';
require_once './config/Database.php';

header ('Content-Type: application/json');

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $sistema = new Sistema($db);
    if ($sistema->darBaixaPosVenda($_GET['id'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Falha ao atualizar o banco de dados']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
}
?>