<?php
require_once './config/Database.php';
$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? null;

if ($id) {
    $stmtCheck = $db->prepare("SELECT status FROM itens_producao WHERE id = ?");
    $stmtCheck->execute([$id]);
    $atual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($atual) {
        if ($atual['status'] === 'Pendente') {
            $novoStatus = 'Finalizado';
        } else {
            $novoStatus = 'Embalado'; 
        }

        $query = "UPDATE itens_producao SET status = :status, data_fim = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $novoStatus);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'novoStatus' => $novoStatus]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}