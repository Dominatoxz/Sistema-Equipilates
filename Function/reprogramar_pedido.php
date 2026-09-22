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
    $numeroPedido = $pedido['numero_pedido'];

    $db->beginTransaction();

    $stmtDelete = $db->prepare("DELETE FROM pedidos_prontos WHERE id = ?");
    $stmtDelete->execute([$idPedido]);

    if ($stmtDelete->rowCount() === 0) {
        // Outra pessoa ja removeu essa mesma linha (duplo clique / outra aba).
        // Sem essa checagem, o pedido acabava com varios registros duplicados
        // em pedidos_reprogramados sem nenhuma linha de verdade pra remover.
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Este pedido já não está mais nesta fila (outra pessoa já deve ter removido ele). Recarregue a página.']);
        exit;
    }

    $stmtInsert = $db->prepare("INSERT INTO pedidos_reprogramados
        (numero_pedido, prazo_producao, origem_tela, motivo, usuario_id, usuario_nome)
        VALUES (:numero_pedido, :prazo_producao, :origem_tela, :motivo, :usuario_id, :usuario_nome)");
    $stmtInsert->execute([
        ':numero_pedido'  => $numeroPedido,
        ':prazo_producao' => $pedido['prazo_producao'],
        ':origem_tela'    => $origemTela,
        ':motivo'         => $motivo,
        ':usuario_id'     => $usuarioId,
        ':usuario_nome'   => $usuarioNome,
    ]);

    // "Remover Pedido" agora tira o pedido do sistema inteiro (quadros, itens
    // de producao/OS, filas de prontos/expedidos e reimpressao pendente) -
    // o unico rastro que sobra e o registro em pedidos_reprogramados acima.
    $db->prepare("DELETE FROM tabela_adaptada WHERE `NUMERO PEDIDO` = ?")->execute([$numeroPedido]);
    $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = ?")->execute([$numeroPedido]);
    $db->prepare("DELETE FROM itens_os WHERE numero_pedido = ?")->execute([$numeroPedido]);
    $db->prepare("DELETE FROM pedidos_expedidos WHERE numero_pedido = ?")->execute([$numeroPedido]);
    $db->prepare("DELETE FROM reimpressao_manual WHERE numero_pedido = ?")->execute([$numeroPedido]);

    $db->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('reprogramar_pedido: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro ao processar a solicitação. Tente novamente.']);
}
