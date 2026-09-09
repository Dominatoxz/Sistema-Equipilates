<?php
// Script de uso único, pra testar a lógica de Reprovado/Retrabalho na
// impressão automática sem precisar de uma decisão real no Telegram.

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
$acao = $_GET['acao'] ?? 'inserir';

if ($acao === 'remover') {
    $stmt = $db->prepare("DELETE FROM itens_producao WHERE numero_pedido IN ('TESTE-REPROVADO', 'TESTE-RETRABALHO')");
    $stmt->execute();
    echo json_encode(['success' => true, 'removidos' => $stmt->rowCount()]);
    exit;
}

// Reprovado, já liberado pela liderança: deve entrar na fila (-PQ/-EQ).
$db->prepare("INSERT INTO itens_producao
    (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status, status_qualidade, qualidade_tentativas, reimpressao_liberada)
    VALUES ('TESTE-REPROVADO', '10/09/2026', 'ITEM TESTE QUALIDADE', 1, 'TESTE', 'Pendente', 'Reprovado', 1, 1)
    ON DUPLICATE KEY UPDATE status = 'Pendente', status_qualidade = 'Reprovado', qualidade_tentativas = 1, reimpressao_liberada = 1")
   ->execute();

// Retrabalho: reprovado mas SEM liberação da liderança — não deve entrar na fila.
$db->prepare("INSERT INTO itens_producao
    (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status, status_qualidade, qualidade_tentativas, reimpressao_liberada)
    VALUES ('TESTE-RETRABALHO', '10/09/2026', 'ITEM TESTE QUALIDADE', 1, 'TESTE', 'Pendente', 'Reprovado', 1, 0)
    ON DUPLICATE KEY UPDATE status = 'Pendente', status_qualidade = 'Reprovado', qualidade_tentativas = 1, reimpressao_liberada = 0")
   ->execute();

echo json_encode(['success' => true]);
