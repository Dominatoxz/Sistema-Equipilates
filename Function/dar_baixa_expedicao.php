<?php
require_once '../Model/Sistema.php';
require_once '../config/Database.php';

header ('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if ($id) {
    try{
        $database = new Database();
        $db = $database->getConnection();

        $query = "UPDATE pedidos_prontos SET status_posvenda = 'Finalizado' WHERE id = ?";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$id])){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Falha ao encaminhar para a Expedicao']);
        }
    } catch (PDOException $e){
        echo json_encode(['success' => false, 'erros' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID Inválido']);
}
?>