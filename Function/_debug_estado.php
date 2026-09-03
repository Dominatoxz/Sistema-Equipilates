<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');
$db = (new Database())->getConnection();
$out = [];

try {
    $out['colunas_itens_producao'] = $db->query("SHOW COLUMNS FROM itens_producao")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $out['erro_colunas'] = $e->getMessage();
}

try {
    $out['produzidos_recentes'] = $db->query(
        "SELECT id, numero_pedido, equipamento, status, status_qualidade, qualidade_tentativas, data_inicio
         FROM itens_producao
         WHERE status = 'Produzido'
         ORDER BY data_inicio DESC
         LIMIT 10"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $out['erro_produzidos'] = $e->getMessage();
}

try {
    $out['qualidade_telegram_chats'] = $db->query("SELECT * FROM qualidade_telegram_chats")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $out['erro_chats'] = $e->getMessage();
}

echo json_encode($out, JSON_PRETTY_PRINT);
