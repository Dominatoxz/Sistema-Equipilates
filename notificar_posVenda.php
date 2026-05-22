<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './config/Database.php';

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

$pedido = $_GET['pedido'] ?? null;

if (!$pedido) {
    echo json_encode(['success' => false, 'error' => 'Pedido não informado.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $totalPendentes = 0;
    $isOs = (stripos($pedido, 'os') !== false);

    if ($isOs) {
        $sqlOs = 'SELECT COUNT(*) FROM itens_os WHERE numero_pedido = ? AND status != "Embalado"';
        $stmtOs = $db->prepare($sqlOs);
        $stmtOs->execute([$pedido]);
        $totalPendentes = (int)$stmtOs->fetchColumn();
    } else {
        $equipamentosPrincipais = [
            'Reformer Excellence', 
            'Reformer Torre', 
            'Cadilac Excelence', 
            'Step Chair Excelence', 
            'Lader Barrel Excelence'
        ];
        
        $placeholders = implode(',', array_fill(0, count($equipamentosPrincipais), '?'));
        
        $sqlProd = "SELECT COUNT(*) FROM itens_producao 
                    WHERE numero_pedido = ? 
                    AND equipamento IN ($placeholders) 
                    AND status != 'Embalado'";
                    
        $stmtProd = $db->prepare($sqlProd);
        
        $params = array_merge([$pedido], $equipamentosPrincipais);
        $stmtProd->execute($params);
        $totalPendentes = (int)$stmtProd->fetchColumn();
    }

    if ($totalPendentes === 0) {
        
        $sqlCheck = 'SELECT COUNT(*) FROM pedidos_prontos WHERE numero_pedido = ?';
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute([$pedido]);
        $existe = $stmtCheck->fetchColumn();

        if (!$existe) {
            $stmtPrazo = $db->prepare("SELECT `PRAZO DE PRODUÇÃO` FROM tabela_adaptada WHERE `NUMERO PEDIDO` = ? LIMIT 1");
            $stmtPrazo->execute([$pedido]);
            $prazo = $stmtPrazo->fetchColumn() ?: 'Sem prazo';

        
            $sqlInsert = "INSERT INTO pedidos_prontos (numero_pedido, prazo_producao, data_conclusao, status_posvenda) 
                          VALUES (?, ?, NOW(), 'Pendente')";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([$pedido, $prazo]);
        }
        

        echo json_encode(['success' => true, 'status_pedido' => 'SUBIU_POS_VENDA']);
    } else {
        echo json_encode([
            'success' => true, 
            'status_pedido' => 'AGUARDANDO_OUTRO_QUADRO', 
            'pendentes' => $totalPendentes
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>