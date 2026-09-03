<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');
$db = (new Database())->getConnection();
try {
    $stmt = $db->query('SELECT id, nome, chat_id, ativo FROM qualidade_telegram_chats');
    echo json_encode(['ok' => true, 'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
