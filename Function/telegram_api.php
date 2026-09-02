<?php

/**
 * Chamada simples à Bot API do Telegram (sendMessage, editMessageText,
 * answerCallbackQuery, etc). Sem lib externa — mesmo espírito do resto do
 * projeto, que não usa um wrapper HTTP genérico (ver disparar_notificacao.php).
 *
 * @return array Resposta decodificada da API (ou ['ok' => false, 'error' => ...] em falha de rede).
 */
function telegramApiCall(string $token, string $method, array $params): array
{
    $url = "https://api.telegram.org/bot{$token}/{$method}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $resposta = curl_exec($ch);
    $erro = curl_error($ch);
    curl_close($ch);

    if ($resposta === false) {
        return ['ok' => false, 'error' => $erro];
    }

    $decodificado = json_decode($resposta, true);
    return is_array($decodificado) ? $decodificado : ['ok' => false, 'error' => 'Resposta inválida da API do Telegram.'];
}
