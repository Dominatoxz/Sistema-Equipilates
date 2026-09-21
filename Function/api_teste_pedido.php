<?php
require_once '../global.php';
require_once '../config/Database.php';
header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';
if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

$pedido = (string) ($_GET['pedido'] ?? '');
$db = (new Database())->getConnection();
$out = [];
foreach (['itens_producao' => 'PRODUCAO', 'itens_os' => 'OS'] as $tabela => $origem) {
    $st = $db->prepare("SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, status, status_qualidade, qualidade_tentativas, reimpressao_liberada FROM $tabela WHERE numero_pedido = ? ORDER BY equipamento, posicao_no_pedido");
    $st->execute([$pedido]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $it) {
        $sp = $db->prepare("SELECT tipo_etiqueta, usuario_nome, motivo_reimpressao, data_impressao FROM impressoes_etiquetas WHERE id_item = ? AND tabela_origem = ? ORDER BY 4");
        try {
            $sp->execute([$it['id'], $origem]);
            $it['impressoes'] = $sp->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $it['impressoes'] = 'erro: ' . $e->getMessage();
        }
        $it['tabela_origem'] = $origem;
        $out[] = $it;
    }
}
echo json_encode(['success' => true, 'itens' => $out], JSON_UNESCAPED_UNICODE);
