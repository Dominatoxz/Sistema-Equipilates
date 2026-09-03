<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/telegram_api.php';

header('Content-Type: application/json');
$token = getenv('TELEGRAM_QUALIDADE_BOT_TOKEN');
$chatIdMsg = 5920819250;
echo json_encode([
    'token_presente' => !empty($token),
    'sendMessage' => telegramApiCall($token, 'sendMessage', [
        'chat_id' => $chatIdMsg,
        'text' => "👋 Seu chat_id é: `{$chatIdMsg}`\nPassa esse número pra ser cadastrado em qualidade_telegram_chats.",
        'parse_mode' => 'Markdown',
    ]),
]);
