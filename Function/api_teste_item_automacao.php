<?php
// Script de uso único, pra criar/remover um item fictício e testar a
// impressão automática de ponta a ponta. Remover do servidor depois de usar.

require_once '../global.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';

if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$db = (new Database())->getConnection();
$acao = $_GET['acao'] ?? 'inserirqm';

if ($acao === 'inserirqm') {
    $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'")->execute();

    $db->prepare("INSERT INTO itens_producao
        (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status, status_qualidade, qualidade_tentativas, reimpressao_liberada)
        VALUES ('TESTE-AUTO-QM', '16/09/2026', 'TESTE QM ETIQUETA 2', 1, 'TESTE', 'Pendente', 'Reprovado', 1, 1)")
       ->execute();
    $idNovo = (int) $db->lastInsertId();

    $db->prepare("INSERT INTO qualidade_inspecoes (tabela_origem, item_id, tentativa, decisao, motivo, telegram_user, telegram_chat_id, qm_code)
        VALUES ('itens_producao', :item_id, 1, 'Reprovado', 'Teste de etiqueta de identificacao (Claude)', 'ClaudeTeste', 'teste-script', :qm_code)")
       ->execute([':item_id' => $idNovo, ':qm_code' => 'QM-TESTE-CLAUDE-' . $idNovo]);

    echo json_encode(['success' => true, 'id' => $idNovo]);
    exit;
}

if ($acao === 'debug') {
    $stmt = $db->prepare("SELECT * FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'");
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    $qi = null;
    if ($item) {
        $stmtQi = $db->prepare("SELECT * FROM qualidade_inspecoes WHERE tabela_origem='itens_producao' AND item_id = ?");
        $stmtQi->execute([$item['id']]);
        $qi = $stmtQi->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmtImp = $db->prepare("SELECT * FROM impressoes_etiquetas WHERE id_item = ?");
    $stmtImp->execute([$item['id'] ?? 0]);
    echo json_encode(['success' => true, 'item' => $item, 'qualidade_inspecoes' => $qi, 'impressoes' => $stmtImp->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($acao === 'remover') {
    $stmtBuscaQm = $db->prepare("SELECT id FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'");
    $stmtBuscaQm->execute();
    $idQm = $stmtBuscaQm->fetchColumn();
    if ($idQm) {
        $db->prepare("DELETE FROM qualidade_inspecoes WHERE tabela_origem = 'itens_producao' AND item_id = :id")->execute([':id' => $idQm]);
    }
    $stmt = $db->prepare("DELETE FROM itens_producao WHERE numero_pedido = 'TESTE-AUTO-QM'");
    $stmt->execute();
    echo json_encode(['success' => true, 'removidos' => $stmt->rowCount()]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'acao desconhecida']);
