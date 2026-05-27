<?php
require_once '../Model/Sistema.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $sqlBusca = "SELECT numero_pedido, prazo_producao FROM pedidos_prontos WHERE id = ?";
        $stmtBusca = $db->prepare($sqlBusca);
        $stmtBusca->execute([$id]);
        $dadosPedido = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if (!$dadosPedido) {
            echo json_encode(['success' => false, 'error' => 'Pedido não encontrado ou já expedido.']);
            exit;
        }

        $numeroPedidoReal = $dadosPedido['numero_pedido'];
        $prazoProducao = $dadosPedido['prazo_producao'];

        $db->beginTransaction();

        $queryInsert = "INSERT INTO pedidos_expedidos (numero_pedido, prazo_producao, data_conclusao, status_posvenda)
                        VALUES (?, ?, NOW(), 'Finalizado')";
        $stmtInsert = $db->prepare($queryInsert);
        $stmtInsert->execute([$numeroPedidoReal, $prazoProducao]);

        $queryUpdate = "UPDATE pedidos_prontos SET status_posvenda = 'Expedido' WHERE id = ?";
        $stmtUpdate = $db->prepare($queryUpdate);
        $stmtUpdate->execute([$id]);

        /* // OPÇÃO B: Se você preferir DELETAR o registro da tabela temporária em vez de dar UPDATE, 
        // comente as linhas do UPDATE acima e use estas duas linhas abaixo:
        $queryDelete = "DELETE FROM pedidos_prontos WHERE id = ?";
        $stmtDelete = $db->prepare($queryDelete);
        $stmtDelete->execute([$id]);
        */

        $db->commit();

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'error' => 'Erro no Banco: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID Inválido ou não enviado.']);
}
?>