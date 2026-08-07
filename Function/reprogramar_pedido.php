<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

if (!validarTokenCSRF($dados['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada ou inválida. Recarregue a página e tente de novo.']);
    exit;
}

$idPedido   = isset($dados['id_pedido']) ? (int)$dados['id_pedido'] : 0;
$motivo     = isset($dados['motivo']) ? trim($dados['motivo']) : '';
$origemTela = isset($dados['origem_tela']) ? trim($dados['origem_tela']) : '';

if (!$idPedido || $motivo === '' || $origemTela === '') {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos. Informe o motivo da reprogramação.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmtBusca = $db->prepare("SELECT numero_pedido, prazo_producao FROM pedidos_prontos WHERE id = ?");
    $stmtBusca->execute([$idPedido]);
    $pedido = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'error' => 'Pedido não encontrado.']);
        exit;
    }

    $usuarioId   = $_SESSION['usuario_id'] ?? null;
    $usuarioNome = $_SESSION['usuario_logado'] ?? null;

    $db->beginTransaction();

    $stmtInsert = $db->prepare("INSERT INTO pedidos_reprogramados
        (numero_pedido, prazo_producao, origem_tela, motivo, usuario_id, usuario_nome)
        VALUES (:numero_pedido, :prazo_producao, :origem_tela, :motivo, :usuario_id, :usuario_nome)");
    $stmtInsert->execute([
        ':numero_pedido'  => $pedido['numero_pedido'],
        ':prazo_producao' => $pedido['prazo_producao'],
        ':origem_tela'    => $origemTela,
        ':motivo'         => $motivo,
        ':usuario_id'     => $usuarioId,
        ':usuario_nome'   => $usuarioNome,
    ]);

    $stmtDelete = $db->prepare("DELETE FROM pedidos_prontos WHERE id = ?");
    $stmtDelete->execute([$idPedido]);

    $db->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
