<?php
/**
 * Rotina de importação da planilha semanal (Contemporâneo ou Clássico) pra
 * dentro de tabela_adaptada + execução do procedimento que gera os itens em
 * itens_producao/itens_os. Uso via linha de comando:
 *
 *   php rotina_importar_planilha.php tipo=classico csv=C:\DB\arquivo.csv
 *   php rotina_importar_planilha.php tipo=contemporaneo csv=C:\DB\arquivo.csv
 *
 * O CSV precisa estar no formato-padrão de tabela_adaptada (mesmas colunas,
 * separador ";", cabeçalho na 1ª linha) — ver C:\DB\ pros exemplos já usados.
 *
 * Travado pra só rodar no banco do clone (producao.sistemaequipilates.com);
 * recusa de propósito se detectar o banco de produção de verdade.
 */
require_once __DIR__ . '/../global.php';
require_once __DIR__ . '/../config/Database.php';

const DB_PRODUCAO_REAL = 'u109029190_db_GN6XeAZr';

const PROCEDURES = [
    'contemporaneo' => 'gerar_unidades_producao',
    'classico' => 'gerar_unidades_producao_classico',
];

// Linhas de rodapé/resumo da planilha que não são pedidos de verdade — nunca
// importar, mesmo que tenham algo preenchido no NUMERO PEDIDO (Vitor,
// 2026-09-11: a linha "somatorio" virou um pedido fantasma e gerou 500 itens
// de produção que não existiam).
const LINHAS_IGNORADAS = ['somatorio', 'total', 'totais'];

function argValor(array $argv, string $chave): ?string
{
    foreach ($argv as $arg) {
        if (str_starts_with($arg, "$chave=")) {
            return substr($arg, strlen($chave) + 1);
        }
    }
    return null;
}

$tipo = argValor($argv, 'tipo');
$csvPath = argValor($argv, 'csv');

if (!$tipo || !isset(PROCEDURES[$tipo])) {
    die("Uso: php rotina_importar_planilha.php tipo=classico|contemporaneo csv=CAMINHO.csv\n");
}
if (!$csvPath || !file_exists($csvPath)) {
    die("CSV não encontrado: $csvPath\n");
}

$db = (new Database())->getConnection();

$dbAtual = getenv('DB_NAME');
if ($dbAtual === DB_PRODUCAO_REAL) {
    die("RECUSADO: isso aqui parece ser o banco de PRODUÇÃO de verdade ($dbAtual). Essa rotina só roda no clone.\n");
}
echo "Banco: $dbAtual (ok, não é produção)\n";

// --- 1. Importa o CSV pra tabela_adaptada ---
$stmtCols = $db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tabela_adaptada' ORDER BY ORDINAL_POSITION");
$colunas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
if (!$colunas) {
    die("ERRO: tabela_adaptada não existe nesse banco.\n");
}

function converteUtf8(?string $valor): ?string
{
    if ($valor === null) return null;
    if (mb_check_encoding($valor, 'UTF-8')) return $valor;
    return @iconv('CP1252', 'UTF-8//IGNORE', $valor);
}

$antesAdaptada = (int) $db->query("SELECT COUNT(*) FROM tabela_adaptada")->fetchColumn();

$fh = fopen($csvPath, 'r');
fgetcsv($fh, 0, ';'); // descarta cabeçalho

$placeholders = implode(',', array_fill(0, count($colunas), '?'));
$colunasEsc = implode(',', array_map(fn($c) => "`$c`", $colunas));
$stmtInsert = $db->prepare("INSERT INTO tabela_adaptada ($colunasEsc) VALUES ($placeholders)");

$inseridas = 0;
$puladas = 0;
while (($row = fgetcsv($fh, 0, ';')) !== false) {
    $numeroPedido = trim($row[0] ?? '');
    $prazo = trim($row[1] ?? '');

    if ($numeroPedido === '' && $prazo === '') {
        $puladas++;
        continue;
    }
    if (in_array(mb_strtolower($numeroPedido), LINHAS_IGNORADAS, true)) {
        $puladas++;
        continue;
    }

    $valores = [];
    for ($i = 0; $i < count($colunas); $i++) {
        $v = $row[$i] ?? null;
        if ($v === '') $v = null;
        $valores[] = converteUtf8($v);
    }
    $stmtInsert->execute($valores);
    $inseridas++;
}
fclose($fh);

$depoisAdaptada = (int) $db->query("SELECT COUNT(*) FROM tabela_adaptada")->fetchColumn();

echo "\n--- Importação CSV ---\n";
echo "Linhas inseridas: $inseridas\n";
echo "Linhas puladas (vazias/totais): $puladas\n";
echo "tabela_adaptada: $antesAdaptada -> $depoisAdaptada\n";

// --- 2. Roda o procedimento certo ---
$procedure = PROCEDURES[$tipo];
$existe = $db->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = '$procedure'")->fetch(PDO::FETCH_ASSOC);
if (!$existe) {
    die("ERRO: procedure $procedure não existe nesse banco.\n");
}

$antesProducao = (int) $db->query("SELECT COUNT(*) FROM itens_producao")->fetchColumn();
$antesOs = (int) $db->query("SELECT COUNT(*) FROM itens_os")->fetchColumn();

$db->exec("CALL $procedure()");

$depoisProducao = (int) $db->query("SELECT COUNT(*) FROM itens_producao")->fetchColumn();
$depoisOs = (int) $db->query("SELECT COUNT(*) FROM itens_os")->fetchColumn();

echo "\n--- Procedure $procedure() ---\n";
echo "itens_producao: $antesProducao -> $depoisProducao (+" . ($depoisProducao - $antesProducao) . ")\n";
echo "itens_os: $antesOs -> $depoisOs (+" . ($depoisOs - $antesOs) . ")\n";

echo "\nConcluído.\n";
