<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';

$pedido = $_GET['pedido'] ?? null;

if (!$pedido) {
    echo json_encode(['success' => false, 'error' => 'Pedido não informado.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $isOS = (stripos($pedido, 'os') !== false);
    $tabelaItens = $isOS ? 'itens_os' : 'itens_producao';

    $sql = "SELECT COUNT(*) FROM $tabelaItens
            WHERE numero_pedido = ?
              AND equipamento NOT LIKE 'Emb.%'
              AND status != 'Armazenado'";
    $stmt = $db->prepare($sql);
    $stmt->execute([$pedido]);
    $totalPendentes = (int) $stmt->fetchColumn();

    if ($totalPendentes === 0) {

        $sqlCheck = 'SELECT COUNT(*) FROM pedidos_prontos WHERE numero_pedido = ?';
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute([$pedido]);
        $existe = $stmtCheck->fetchColumn();

        if (!$existe) {
            if ($isOS) {
                $stmtPrazo = $db->prepare("SELECT prazo_producao FROM itens_os WHERE numero_pedido = ?");
                $stmtPrazo->execute([$pedido]);
                $prazoOriginal = $stmtPrazo->fetchColumn();

                $prazo = $prazoOriginal ? trim($prazoOriginal) : 'Sem prazo';
            } else {
                $stmtPrazo = $db->prepare("SELECT `PRAZO DE PRODUCAO` FROM tabela_adaptada WHERE `NUMERO PEDIDO` = ?");
                $stmtPrazo->execute([$pedido]);
                $prazoOriginal = $stmtPrazo->fetchColumn();

                $prazo = $prazoOriginal ? trim($prazoOriginal) : 'Sem prazo';
            }

            $sqlInsert = "INSERT INTO pedidos_prontos (numero_pedido, prazo_producao, data_conclusao, status_posvenda)
                            VALUES (?, ?, NOW(), 'Financeiro')";
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
