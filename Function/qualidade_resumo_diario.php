<?php
/**
 * Resumo diário do gate de qualidade, mandado pra quem é 'lideranca' em
 * qualidade_telegram_chats. Script standalone (sem HTTP), pensado pra rodar
 * via Cron Job do hPanel (ex.: 0 18 * * *, todo dia às 18h).
 *
 * RECONSTRUÍDO em 2026-09-09 depois que um deploy externo (git checkout pra
 * commit 00d13dc) apagou este arquivo — ele nunca chegou a ser commitado no
 * git do Dominatoxz. Reescrito a partir do histórico da conversa.
 */
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/telegram_api.php';
require_once __DIR__ . '/qualidade_consultas.php';

$token = getenv('TELEGRAM_QUALIDADE_BOT_TOKEN');
if (empty($token)) {
    error_log('qualidade_resumo_diario: TELEGRAM_QUALIDADE_BOT_TOKEN não configurado.');
    exit(1);
}

$db = (new Database())->getConnection();
$hoje = date('Y-m-d');

$stmtLiderancas = $db->prepare("SELECT chat_id FROM qualidade_telegram_chats WHERE ativo = 1 AND tipo = 'lideranca'");
$stmtLiderancas->execute();
$chatsLiderancas = $stmtLiderancas->fetchAll(PDO::FETCH_COLUMN);
if (empty($chatsLiderancas)) {
    error_log('qualidade_resumo_diario: nenhuma liderança cadastrada/ativa — resumo não enviado.');
    exit(0);
}

// Contagem de decisões de HOJE, por bucket.
$stmtHoje = $db->prepare(
    "SELECT decisao, COUNT(*) AS total FROM qualidade_inspecoes WHERE DATE(criado_em) = :hoje GROUP BY decisao"
);
$stmtHoje->execute([':hoje' => $hoje]);
$contagemHoje = ['Aprovado' => 0, 'Retrabalho' => 0, 'Reprovado' => 0];
foreach ($stmtHoje->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $contagemHoje[$row['decisao']] = (int) $row['total'];
}
$totalHoje = array_sum($contagemHoje);

$aguardando = qualidadeListaAguardando($db);
$totalAguardando = array_sum(array_map(fn($p) => count($p['itens']), $aguardando));

$ranking = qualidadeInspetoresRankingDia($db, $hoje);

$linhas = [
    "📊 <b>Resumo diário — Qualidade</b> ({$hoje})",
    '',
    "Hoje: ✅ {$contagemHoje['Aprovado']} aprovado(s) · 🔧 {$contagemHoje['Retrabalho']} retrabalho(s) · ❌ {$contagemHoje['Reprovado']} reprovado(s) — {$totalHoje} decisão(ões) no total.",
    "Aguardando validação agora: {$totalAguardando} item(ns) em " . count($aguardando) . ' pedido(s).',
];

if ($totalAguardando > 0) {
    $linhas[] = '';
    $linhas[] = qualidadeListaAguardandoTexto($aguardando);
}

if ($ranking) {
    $linhas[] = '';
    $linhas[] = qualidadeRankingDiaTexto($ranking);
}

$texto = implode("\n", $linhas);

foreach ($chatsLiderancas as $chatIdLideranca) {
    $resposta = telegramApiCall($token, 'sendMessage', [
        'chat_id' => $chatIdLideranca,
        'text' => $texto,
        'parse_mode' => 'HTML',
    ]);
    if (empty($resposta['ok'])) {
        error_log('qualidade_resumo_diario: falha ao enviar para chat ' . $chatIdLideranca . ': ' . json_encode($resposta));
    }
}

echo "Resumo diário enviado para " . count($chatsLiderancas) . " liderança(s).\n";
