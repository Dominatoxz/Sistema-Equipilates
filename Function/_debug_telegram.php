<?php
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/telegram_api.php';

header('Content-Type: application/json');
$token = getenv('TELEGRAM_QUALIDADE_BOT_TOKEN');
echo json_encode([
    'token_presente' => !empty($token),
    'getMe' => telegramApiCall($token, 'getMe', []),
]);
