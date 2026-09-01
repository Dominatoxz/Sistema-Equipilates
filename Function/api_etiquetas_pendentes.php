<?php
require_once '../global.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';

date_default_timezone_set('America/Sao_Paulo');
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

$inicioSemana = new DateTime('next monday');
$fimSemana = (clone $inicioSemana)->modify('+6 days');
$paramDataIni = $inicioSemana->format('Y-m-d');
$paramDataFim = $fimSemana->format('Y-m-d');

$sql = "
    SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'PRODUCAO' AS tabela_origem
    FROM itens_producao
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
      AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') BETWEEN :data_ini1 AND :data_fim1
    UNION ALL
    SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'OS' AS tabela_origem
    FROM itens_os
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
      AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') BETWEEN :data_ini2 AND :data_fim2
    ORDER BY equipamento ASC, STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC, id ASC
";
$stmtItens = $db->prepare($sql);
$stmtItens->execute([
    ':data_ini1' => $paramDataIni,
    ':data_fim1' => $paramDataFim,
    ':data_ini2' => $paramDataIni,
    ':data_fim2' => $paramDataFim,
]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

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
    // ^CI28 já ativa UTF-8 na etiqueta, então mantém acentos — só escapa
    // os caracteres que o ZPL usa como controle (^ e ~).
    return str_replace(['^', '~'], ['', ''], $texto);
}

function montarZpl(array $item, string $tipo, bool $misto): string
{
    // Reproduz a mesma hierarquia visual da tela manual (imprimir_etiquetas.php):
    // barra lateral marcando o tipo (lá é cor cinza/preta; na térmica, que só
    // imprime preto, viram fina/grossa), cabeçalho com pedido + badge MISTO à
    // esquerda e tipo à direita, linha separadora, título do equipamento em
    // destaque, sub-linha peça/prazo, cor, e código de barras no rodapé.
    $ehEmbalagem  = $tipo === 'EMBALAGEM';
    $tituloTipo   = $ehEmbalagem ? 'EMBALAGEM' : 'PRODUCAO';
    $larguraBarra = $ehEmbalagem ? 50 : 14;
    $baseX        = $larguraBarra + 24;
    $larguraUtil  = 807 - $baseX - 20;

    $numeroPedido = zplEscape('PEDIDO #' . $item['numero_pedido']);
    $equipamento  = zplEscape($item['equipamento']);
    $prazo        = zplEscape(substr((string) $item['prazo_producao'], 0, 10));
    $subLinha     = zplEscape('Peça: ' . $item['posicao_no_pedido'] . ' | Prazo: ' . $prazo);
    $corExibir    = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? $item['cor'] : 'NAO INFORMADA';
    $corLinha     = zplEscape('Cor: ' . $corExibir);
    $sufixo       = $ehEmbalagem ? 'E' : 'P';
    $codigo       = codigoBarra($item, $sufixo);

    $zpl = "^XA\n^PW807\n^LL400\n^CI28\n";
    $zpl .= "^FO0,0^GB{$larguraBarra},400,{$larguraBarra}^FS\n";
    $zpl .= "^FO{$baseX},18^A0N,38,38^FD{$numeroPedido}^FS\n";
    $zpl .= "^FO{$baseX},18^A0N,28,28^FB{$larguraUtil},1,0,R^FD{$tituloTipo}^FS\n";
    if ($misto) {
        $zpl .= "^FO{$baseX},62^GB90,26,26^FS\n";
        $zpl .= "^FO" . ($baseX + 10) . ",66^FR^A0N,18,18^FDMISTO^FS\n";
    }
    $zpl .= "^FO{$baseX},96^GB{$larguraUtil},2,2^FS\n";
    $zpl .= "^FO{$baseX},108^A0N,30,30^FD{$equipamento}^FS\n";
    $zpl .= "^FO{$baseX},148^A0N,22,22^FD{$subLinha}^FS\n";
    $zpl .= "^FO{$baseX},176^A0N,22,22^FD{$corLinha}^FS\n";
    $zpl .= "^FO{$baseX},204^A0N,22,22^FB{$larguraUtil},1,0,C^FDID: {$codigo}^FS\n";
    $zpl .= "^BY7\n^FO" . ($baseX + 20) . ",232^BCN,90,N,N,N^FD{$codigo}^FS\n";
    $zpl .= "^XZ\n";

    return $zpl;
}

$jobs = [];

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
