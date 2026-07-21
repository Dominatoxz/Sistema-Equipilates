<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

$pedido = $_GET['pedido'] ?? null;
$origem = $_GET['origem'] ?? null;

if (!$pedido || !$origem) {
    echo json_encode(['success' => false, 'error' => 'Parametros insuficientes']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($origem === 'OS') {
        $tabela = 'itens_os';
        $query = "SELECT equipamento AS nome_produto, status FROM $tabela WHERE numero_pedido = :pedido AND equipamento NOT LIKE '%Emb.%'";
    } else {
        $tabela = 'itens_producao';
        $query = "SELECT equipamento AS nome_produto, status FROM $tabela WHERE numero_pedido = :pedido AND equipamento NOT LIKE '%Emb.%'";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':pedido', $pedido);
    $stmt->execute();
    $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $itensFormatados = [];
    foreach ($linhas as $l) {
        $itensFormatados[] = [
            'nome' => $l['nome_produto'] ?? 'Item sem identificação',
            'status' => trim($l['status'])
        ];
    }

    echo json_encode(['success' => true, 'itens' => $itensFormatados]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
