<?php
require_once './config/Database.php';

header('Content-Type: application/json');

if (isset($_GET['pedido'])) {
    $numero_pedido = $_GET['pedido'];

    $database = new Database();
    $db = $database->getConnection();

    try {
        $stmt_prazo = $db->prepare("SELECT 'PRAZO DE PRODUÇÃO' from tabela_adaptada WHERE `NUMERO PEDIDO` = ? LIMIT 1");
        $stmt_prazo->execute([$numero_pedido]);
        $prazo = $stmt_prazo->fetchColumn() ?: 'Sem prazo';

        $query = "INSERT IGNORE INTO pedidos_prontos (numero_pedido, prazo_producao) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$numero_pedido, $prazo]);
    
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Número do pedido não fornecido']);
}




?>