<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php';
require_once '../Function/trava.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function enviarNotificacaoPorSetor($perfisPermitidos, $titulo, $mensagem, $urlDestino) {
    $db = (new Database())->getConnection();

    $in = str_repeat('?,', count($perfisPermitidos) - 1) . '?';
    $stmt = $db->prepare("SELECT endpoint, p256dh, auth FROM push_subscriptions WHERE nivel_acesso IN ($in)");
    $stmt->execute($perfisPermitidos);
    $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($inscritos)) return;

    $auth = [
        'VAPID' => [
            'subject' => 'mailto:matheusdominato@equipilates.com.br',
            'publicKey' => 'BCAC-rK4x0HiHSXAjjgt_GsuRVk4Gj3nZaiAnLxeYebmWZtZb82pb0QmOrF764zwtOauecHHP7BvsxdjKjvVgTM',
            'privateKey' => 'xMr0MFYXxp6wnyLrPiSHk_QpqoeqxMqjYBGe5lOkPFU',
        ],
    ];

    $webPush = new WebPush($auth);
    foreach ($inscritos as $subData) {
        $subscription = Subscription::create([
            'endpoint'  => $subData['endpoint'],
            'publicKey' => $subData['p256dh'],  
            'authToken' => $subData['auth'],
        ]);

        $payload = json_encode([
            'title' => $titulo,
            'body'  => $mensagem,
            'icon'  => '/assets/logo_equipilates.png', 
            'data'  => ['url' => $urlDestino]
        ]);

    $webPush->queueNotification($subscription, $payload);
}

    foreach ($webPush->flush() as $report) {
        if ($report->isSubscriptionExpired()) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $stmtDel = $db->prepare("DELETE FROM push_subscriptions WHERE endpoint = :endpoint");
            $stmtDel->execute([':endpoint' => $endpoint]);
        }
    }
}