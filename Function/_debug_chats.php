<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');
$db = (new Database())->getConnection();
$stmt = $db->query('SELECT id, nome, chat_id, tipo, ativo FROM qualidade_telegram_chats');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
