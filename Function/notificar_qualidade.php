<?php
require_once __DIR__ . '/telegram_api.php';

/*
 * Dispara a mensagem de inspeção de qualidade no Telegram (botões Aprovar/
 * Reprovar) sempre que um item vira Produzido. Espelha o padrão de
 * notificar_pos_producao.php, mas em vez de Web Push chama a Bot API do
 * Telegram diretamente (ver Function/telegram_api.php).
 *
 * Best-effort: falha de rede/token aqui não deve impedir a bipagem de
 * produção, então erros só são registrados, nunca propagados.
 *
 * $tabela: 'itens_producao' ou 'itens_os'.
 */
function notificarQualidade(PDO $db, string $tabela, int $id): void
{
    $token = getenv('TELEGRAM_QUALIDADE_BOT_TOKEN');
    if (empty($token)) {
        error_log('notificarQualidade: TELEGRAM_QUALIDADE_BOT_TOKEN não configurado.');
        return;
    }

    $stmt = $db->prepare("SELECT * FROM $tabela WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        return;
    }

    $stmtChats = $db->prepare('SELECT chat_id FROM qualidade_telegram_chats WHERE ativo = 1');
    $stmtChats->execute();
    $chats = $stmtChats->fetchAll(PDO::FETCH_COLUMN);
    if (empty($chats)) {
        error_log('notificarQualidade: nenhum chat de qualidade cadastrado/ativo.');
        return;
    }

    $tabelaCurta = ($tabela === 'itens_os') ? 'o' : 'p';
    $tentativaAtual = ((int) ($item['qualidade_tentativas'] ?? 0)) + 1;
    $idExibicao = ($tabela === 'itens_os') ? 'OS' . $id : (string) $id;

    $corExibir = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? $item['cor'] : 'Não informada';

    $linhas = [
        "🔎 <b>Inspeção de qualidade</b>",
        "Pedido: <b>" . htmlspecialchars($item['numero_pedido'] ?? '-') . "</b>",
        "Equipamento: " . htmlspecialchars($item['equipamento'] ?? '-'),
        "Peça: " . htmlspecialchars((string) ($item['posicao_no_pedido'] ?? '-')),
        "Cor: " . htmlspecialchars($corExibir),
        "ID: {$idExibicao}",
    ];
    if ($tentativaAtual > 1) {
        $linhas[] = "⚠️ Tentativa nº {$tentativaAtual} (já reprovado antes)";
    }
    $texto = implode("\n", $linhas);

    $tecladoAprovarReprovar = [
        'inline_keyboard' => [[
            ['text' => '✅ Aprovar', 'callback_data' => "q:aprovar:{$tabelaCurta}:{$id}:{$tentativaAtual}"],
            ['text' => '❌ Reprovar', 'callback_data' => "q:reprovar:{$tabelaCurta}:{$id}:{$tentativaAtual}"],
        ]],
    ];

    foreach ($chats as $chatId) {
        $resposta = telegramApiCall($token, 'sendMessage', [
            'chat_id' => $chatId,
            'text' => $texto,
            'parse_mode' => 'HTML',
            'reply_markup' => $tecladoAprovarReprovar,
        ]);
        if (empty($resposta['ok'])) {
            error_log('notificarQualidade: falha ao enviar para chat ' . $chatId . ': ' . json_encode($resposta));
        }
    }
}
