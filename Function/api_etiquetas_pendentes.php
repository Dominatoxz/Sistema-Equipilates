<?php
require_once '../global.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';

header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';

if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$db = (new Database())->getConnection();

$sistema = new Sistema($db);
$pedidosMistos = array_unique(array_merge(
    $sistema->pedidosMistos('itens_producao'),
    $sistema->pedidosMistos('itens_os')
));

$sql = "
    SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'PRODUCAO' AS tabela_origem
    FROM itens_producao
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
    UNION ALL
    SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'OS' AS tabela_origem
    FROM itens_os
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
    ORDER BY equipamento ASC, STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC, id ASC
";
$itens = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Agrupa por equipamento (a query já vem ordenada por equipamento, então
// os grupos ficam contíguos) — dentro de cada grupo a ordem já é
// prazo/pedido/id, exatamente como a tela manual.
$itensPorEquipamento = [];
foreach ($itens as $item) {
    $itensPorEquipamento[trim($item['equipamento'])][] = $item;
}

$stmtJaImpressa = $db->prepare(
    "SELECT 1 FROM impressoes_etiquetas WHERE id_item = :id_item AND tabela_origem = :tabela_origem AND tipo_etiqueta = :tipo_etiqueta LIMIT 1"
);

function jaFoiImpressa(PDOStatement $stmt, int $idItem, string $tabelaOrigem, string $tipoEtiqueta): bool
{
    $stmt->execute([':id_item' => $idItem, ':tabela_origem' => $tabelaOrigem, ':tipo_etiqueta' => $tipoEtiqueta]);
    return (bool) $stmt->fetchColumn();
}

function codigoBarra(array $item, string $sufixo): string
{
    $base = $item['tabela_origem'] === 'OS' ? 'OS' . $item['id'] : $item['id'];
    return $base . '-' . $sufixo;
}

function zplEscape(string $texto): string
{
    $semAcento = iconv('UTF-8', 'ASCII//TRANSLIT', $texto) ?: $texto;
    return str_replace(['^', '~'], ['', ''], $semAcento);
}

function montarZpl(array $item, string $tipo, bool $misto): string
{
    $numeroPedido = zplEscape('PEDIDO #' . $item['numero_pedido'] . ($misto ? ' [MISTO]' : ''));
    $tituloTipo   = $tipo === 'PRODUCAO' ? 'PRODUCAO' : 'EMBALAGEM';
    $equipamento  = zplEscape($item['equipamento']);
    $prazo        = zplEscape(substr((string) $item['prazo_producao'], 0, 10));
    $subLinha     = zplEscape('Peca: ' . $item['posicao_no_pedido'] . ' | Prazo: ' . $prazo);
    $corExibir    = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? $item['cor'] : 'NAO INFORMADA';
    $corLinha     = zplEscape('Cor: ' . $corExibir);
    $sufixo       = $tipo === 'PRODUCAO' ? 'P' : 'E';
    $codigo       = codigoBarra($item, $sufixo);

    return "^XA\n"
        . "^PW807\n"
        . "^LL400\n"
        . "^CI28\n"
        . "^FO20,20^A0N,42,42^FD{$numeroPedido}^FS\n"
        . "^FO20,70^A0N,32,32^FD{$tituloTipo}^FS\n"
        . "^FO20,115^A0N,28,28^FD{$equipamento}^FS\n"
        . "^FO20,150^A0N,22,22^FD{$subLinha}^FS\n"
        . "^FO20,178^A0N,22,22^FD{$corLinha}^FS\n"
        . "^BY3\n"
        . "^FO60,225^BCN,90,Y,N,N^FD{$codigo}^FS\n"
        . "^XZ\n";
}

$jobs = [];

// Por equipamento (ordem alfabética, vinda da query): primeiro todas as
// etiquetas de PRODUÇÃO da semana daquele equipamento, depois todas as de
// EMBALAGEM do mesmo equipamento, só então passa pro próximo equipamento.
foreach ($itensPorEquipamento as $nomeEquipamento => $itensDoEquipamento) {
    $apenasEmbalagem = strcasecmp($nomeEquipamento, 'Carrinho') === 0
        || strcasecmp($nomeEquipamento, 'Gaiola') === 0
        || strcasecmp($nomeEquipamento, 'Gaiola Cadilac') === 0;

    if (!$apenasEmbalagem) {
        foreach ($itensDoEquipamento as $item) {
            $idItem = (int) $item['id'];
            $tabelaOrigem = $item['tabela_origem'];
            if (jaFoiImpressa($stmtJaImpressa, $idItem, $tabelaOrigem, 'PRODUCAO')) {
                continue;
            }
            $jobs[] = [
                'id_item' => $idItem,
                'tabela_origem' => $tabelaOrigem,
                'tipo_etiqueta' => 'PRODUCAO',
                'zpl' => montarZpl($item, 'PRODUCAO', in_array($item['numero_pedido'], $pedidosMistos)),
            ];
        }
    }

    foreach ($itensDoEquipamento as $item) {
        $idItem = (int) $item['id'];
        $tabelaOrigem = $item['tabela_origem'];
        if (jaFoiImpressa($stmtJaImpressa, $idItem, $tabelaOrigem, 'EMBALAGEM')) {
            continue;
        }
        $jobs[] = [
            'id_item' => $idItem,
            'tabela_origem' => $tabelaOrigem,
            'tipo_etiqueta' => 'EMBALAGEM',
            'zpl' => montarZpl($item, 'EMBALAGEM', in_array($item['numero_pedido'], $pedidosMistos)),
        ];
    }
}

echo json_encode(['success' => true, 'jobs' => $jobs]);
