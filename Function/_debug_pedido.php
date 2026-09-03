<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');
$db = (new Database())->getConnection();
$pedido = $_GET['pedido'] ?? '7364';

$stmt = $db->prepare("SELECT id, equipamento, status, status_qualidade, qualidade_tentativas, 'itens_producao' AS tabela FROM itens_producao WHERE numero_pedido = ?");
$stmt->execute([$pedido]);
$producao = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $db->prepare("SELECT id, equipamento, status, status_qualidade, qualidade_tentativas, 'itens_os' AS tabela FROM itens_os WHERE numero_pedido = ?");
$stmt2->execute([$pedido]);
$os = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['pedido' => $pedido, 'itens_producao' => $producao, 'itens_os' => $os], JSON_PRETTY_PRINT);
