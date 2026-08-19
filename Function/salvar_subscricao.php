<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['endpoint'], $data['keys']['p256dh'], $data['keys']['auth'])) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;
$nivelAcesso = $_SESSION['nivel_acesso'] ?? 'convidado';

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    INSERT INTO push_subscriptions (user_id, nivel_acesso, endpoint, p256dh, auth)
    VALUES (:user_id, :nivel, :endpoint, :p256dh, :auth)
    ON DUPLICATE KEY UPDATE 
        p256dh = VALUES(p256dh), 
        auth = VALUES(auth)
");

$success = $stmt->execute([
    ':user_id' => $userId,
    ':nivel' => $nivelAcesso,
    ':endpoint' => $data['endpoint'],
    ':p256dh' => $data['keys']['p256dh'],
    ':auth' => $data['keys']['auth']
]);

echo json_encode(['success' => $success]);