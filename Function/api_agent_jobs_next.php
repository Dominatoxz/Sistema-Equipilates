<?php
/**
 * Adapter pro agente eqagent (10.0.0.18, /opt/equipilates-agent): traduz a
 * lista de etiquetas pendentes (mesma lógica de api_etiquetas_pendentes.php,
 * duplicada aqui de propósito pra não arriscar quebrar aquele endpoint pra
 * quem mais já o use) pro formato de "job" genérico que o agente espera —
 * {"id","type","payload"} — via GET api.jobs_next_path?agent=<nome>.
 *
 * Criado em 2026-09-09: a integração agente<->site nunca tinha sido
 * terminada (poller apontava pra endpoint inexistente /agent/jobs/next,
 * sem token, e o formato de job nunca bateria mesmo com a URL certa — ver
 * api_agent_jobs_result.php pro outro lado da ponte).
 *
 * id do job: "etq:<tabela_origem>:<id_item>:<tipo_etiqueta>" — o agente
 * devolve esse id junto do resultado, e api_agent_jobs_result.php usa ele
 * pra saber o que confirmar em impressoes_etiquetas (o agente não reenvia o
 * payload original no resultado, só {id, type, status, result, ...}).
 */
require_once '../global.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';

date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';

if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido.']);
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
    SELECT t.id, t.numero_pedido, t.equipamento, t.posicao_no_pedido, t.cor, t.prazo_producao, t.qualidade_tentativas, t.reimpressao_liberada, t.status_qualidade, 'PRODUCAO' AS tabela_origem,
           qi.qm_code AS qm_code, qi.motivo AS qm_motivo, qi.telegram_user AS qm_inspetor, qi.criado_em AS qm_decidido_em
    FROM itens_producao t
    LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = 'itens_producao' AND qi.item_id = t.id
      AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = 'itens_producao' AND item_id = t.id)
    WHERE t.status != 'Embalado' AND t.equipamento NOT LIKE 'Emb.%'
      AND (
            (t.status_qualidade = 'Reprovado' AND t.reimpressao_liberada = 1)
            OR (t.status_qualidade != 'Reprovado' AND STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') BETWEEN :data_ini1 AND :data_fim1)
          )
    UNION ALL
    SELECT t.id, t.numero_pedido, t.equipamento, t.posicao_no_pedido, t.cor, t.prazo_producao, t.qualidade_tentativas, t.reimpressao_liberada, t.status_qualidade, 'OS' AS tabela_origem,
           qi.qm_code AS qm_code, qi.motivo AS qm_motivo, qi.telegram_user AS qm_inspetor, qi.criado_em AS qm_decidido_em
    FROM itens_os t
    LEFT JOIN qualidade_inspecoes qi ON qi.tabela_origem = 'itens_os' AND qi.item_id = t.id
      AND qi.tentativa = (SELECT MAX(tentativa) FROM qualidade_inspecoes WHERE tabela_origem = 'itens_os' AND item_id = t.id)
    WHERE t.status != 'Embalado' AND t.equipamento NOT LIKE 'Emb.%'
      AND (
            (t.status_qualidade = 'Reprovado' AND t.reimpressao_liberada = 1)
            OR (t.status_qualidade != 'Reprovado' AND STR_TO_DATE(t.prazo_producao, '%d/%m/%Y') BETWEEN :data_ini2 AND :data_fim2)
          )
    ORDER BY equipamento ASC, STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC, id ASC
";
// Reprovado+liberado é sempre elegível, sem depender do prazo original — ver
// o mesmo comentário/motivo em api_etiquetas_pendentes.php.
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

function ehReimpressaoQualidade(array $item): bool
{
    return ($item['status_qualidade'] ?? null) === 'Reprovado'
        && (int) ($item['reimpressao_liberada'] ?? 0) === 1;
}

function codigoBarra(array $item, string $sufixo): string
{
    $base = $item['tabela_origem'] === 'OS' ? 'OS' . $item['id'] : $item['id'];
    return $base . '-' . $sufixo;
}

function zplEscape(string $texto): string
{
    return str_replace(['^', '~'], ['', ''], $texto);
}

function montarZpl(array $item, string $tipo, bool $misto): string
{
    // Idêntico a api_etiquetas_pendentes.php — ver lá os comentários sobre a
    // hierarquia visual reproduzida.
    $ehEmbalagem  = $tipo === 'EMBALAGEM';
    $tituloTipo   = $ehEmbalagem ? 'EMBALAGEM' : 'PRODUCAO';
    $larguraBarra = 50;
    $baseX        = $larguraBarra + 24;
    $larguraUtil  = 807 - $baseX - 20;
    $moduleWidth  = 5;

    $numeroPedido = zplEscape('PEDIDO #' . $item['numero_pedido']);
    $equipamento  = zplEscape($item['equipamento']);
    $prazo        = zplEscape(substr((string) $item['prazo_producao'], 0, 10));
    $subLinha     = zplEscape('Peça: ' . $item['posicao_no_pedido'] . ' | Prazo: ' . $prazo);
    $corExibir    = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? $item['cor'] : 'NAO INFORMADA';
    $corLinha     = zplEscape('Cor: ' . $corExibir);
    $sufixo       = $ehEmbalagem ? 'E' : 'P';
    if (ehReimpressaoQualidade($item)) {
        $sufixo .= 'Q';
    }
    $codigo       = codigoBarra($item, $sufixo);

    $margemTopo = 25;

    $zpl = "^XA\n^PW807\n^LL400\n^CI28\n";
    $zpl .= "^FO0,0^GB{$larguraBarra},400,{$larguraBarra}^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 18) . "^A0N,38,38^FD{$numeroPedido}^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 18) . "^A0N,28,28^FB{$larguraUtil},1,0,R^FD{$tituloTipo}^FS\n";
    if ($misto) {
        $zpl .= "^FO{$baseX}," . ($margemTopo + 62) . "^GB90,26,26^FS\n";
        $zpl .= "^FO" . ($baseX + 10) . "," . ($margemTopo + 66) . "^FR^A0N,18,18^FDMISTO^FS\n";
    }
    $zpl .= "^FO{$baseX}," . ($margemTopo + 96) . "^GB{$larguraUtil},2,2^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 108) . "^A0N,30,30^FD{$equipamento}^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 148) . "^A0N,22,22^FD{$subLinha}^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 176) . "^A0N,22,22^FD{$corLinha}^FS\n";
    $zpl .= "^FO{$baseX}," . ($margemTopo + 204) . "^A0N,22,22^FB{$larguraUtil},1,0,C^FDID: {$codigo}^FS\n";

    $larguraBarcode = ((11 * strlen($codigo)) + 35) * $moduleWidth;
    $xBarcode = $baseX + max(0, (int) (($larguraUtil - $larguraBarcode) / 2));
    $zpl .= "^BY{$moduleWidth}\n^FO{$xBarcode}," . ($margemTopo + 250) . "^BCN,90,N,N,N^FD{$codigo}^FS\n";
    $zpl .= "^XZ\n";

    return $zpl;
}

/**
 * Etiqueta de identificação do material reprovado (Vitor, 2026-09-09):
 * "precisamos também que uma etiqueta de identificação seja criada para o
 * material recusado". Sai junto com a -PQ/-EQ (mesmo gatilho, mesmo laço) —
 * mostra o número da notificação QM aberta automaticamente, o item, o
 * motivo, quem reprovou e quando. Só chamada quando ehReimpressaoQualidade()
 * E $item['qm_code'] já estiverem preenchidos (ver laço principal). Idêntica
 * à versão em api_etiquetas_pendentes.php.
 */
function montarZplIdentificacao(array $item): string
{
    $larguraBarra = 50;
    $baseX = $larguraBarra + 24;
    $larguraUtil = 807 - $baseX - 20;
    $moduleWidth = 4;

    $idExibicao = ($item['tabela_origem'] === 'OS') ? 'OS' . $item['id'] : $item['id'];
    $qmCode = zplEscape((string) $item['qm_code']);
    $cabecalho = zplEscape('PEDIDO #' . $item['numero_pedido'] . ' — ' . $item['equipamento']);
    $peca = zplEscape('Peça: ' . $item['posicao_no_pedido'] . ' | ID: ' . $idExibicao);
    $motivo = zplEscape('Motivo: ' . (string) ($item['qm_motivo'] ?: '-'));
    $inspetor = zplEscape('Reprovado por: ' . (string) ($item['qm_inspetor'] ?: '-'));
    $dataHora = $item['qm_decidido_em'] ? date('d/m/Y H:i', strtotime($item['qm_decidido_em'])) : '-';
    $dataLinha = zplEscape('Data/hora: ' . $dataHora);

    $zpl = "^XA\n^PW807\n^LL400\n^CI28\n";
    $zpl .= "^FO0,0^GB{$larguraBarra},400,{$larguraBarra}^FS\n";
    $zpl .= "^FO{$baseX},15^A0N,32,32^FDIDENTIFICACAO - REPROVADO^FS\n";
    $zpl .= "^FO{$baseX},53^GB{$larguraUtil},2,2^FS\n";
    $zpl .= "^FO{$baseX},65^A0N,30,30^FDQM: {$qmCode}^FS\n";
    $zpl .= "^FO{$baseX},103^A0N,24,24^FB{$larguraUtil},1,0,L^FD{$cabecalho}^FS\n";
    $zpl .= "^FO{$baseX},133^A0N,20,20^FD{$peca}^FS\n";
    $zpl .= "^FO{$baseX},163^A0N,20,20^FB{$larguraUtil},3,4^FD{$motivo}^FS\n";
    $zpl .= "^FO{$baseX},239^A0N,20,20^FD{$inspetor}^FS\n";
    $zpl .= "^FO{$baseX},265^A0N,20,20^FD{$dataLinha}^FS\n";

    $larguraBarcode = ((11 * strlen($qmCode)) + 35) * $moduleWidth;
    $xBarcode = $baseX + max(0, (int) (($larguraUtil - $larguraBarcode) / 2));
    $zpl .= "^BY{$moduleWidth}\n^FO{$xBarcode},295^BCN,90,N,N,N^FD{$qmCode}^FS\n";
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
            $tentativas = (int) ($item['qualidade_tentativas'] ?? 0);
            $tipoEtiquetaJob = ehReimpressaoQualidade($item) ? "PRODUCAO_Q{$tentativas}" : 'PRODUCAO';
            if (jaFoiImpressa($stmtJaImpressa, $idItem, $tabelaOrigem, $tipoEtiquetaJob)) {
                continue;
            }
            $jobs[] = [
                'id' => "etq:{$tabelaOrigem}:{$idItem}:{$tipoEtiquetaJob}",
                'type' => 'print',
                'payload' => [
                    // ZPL sempre vai pra Zebra (fila raw, ZPL cru) — nunca a
                    // Epson (default de sistema), que não entende ZPL.
                    'printer' => 'Zebra_ZD230',
                    'text' => montarZpl($item, 'PRODUCAO', in_array($item['numero_pedido'], $pedidosMistos)),
                    'copies' => 1,
                ],
            ];
        }
    }

    foreach ($itensDoEquipamento as $item) {
        $idItem = (int) $item['id'];
        $tabelaOrigem = $item['tabela_origem'];
        $tentativas = (int) ($item['qualidade_tentativas'] ?? 0);
        $tipoEtiquetaJob = ehReimpressaoQualidade($item) ? "EMBALAGEM_Q{$tentativas}" : 'EMBALAGEM';
        if (jaFoiImpressa($stmtJaImpressa, $idItem, $tabelaOrigem, $tipoEtiquetaJob)) {
            continue;
        }
        $jobs[] = [
            'id' => "etq:{$tabelaOrigem}:{$idItem}:{$tipoEtiquetaJob}",
            'type' => 'print',
            'payload' => [
                'printer' => 'Zebra_ZD230',
                'text' => montarZpl($item, 'EMBALAGEM', in_array($item['numero_pedido'], $pedidosMistos)),
                'copies' => 1,
            ],
        ];
    }

    // Etiqueta de identificação do reprovado — 1 por item (não por PRODUCAO/
    // EMBALAGEM), só quando já tem notificação QM aberta (qm_code). Se ainda
    // não tiver (bot de verdade, cron do ERP ainda não processou), o item
    // simplesmente não entra nesta rodada — aparece sozinho quando o cron
    // preencher qualidade_inspecoes.qm_code (mesmo padrão "eventualmente
    // consistente" das outras etiquetas de reimpressão).
    foreach ($itensDoEquipamento as $item) {
        if (!ehReimpressaoQualidade($item) || empty($item['qm_code'])) {
            continue;
        }
        $idItem = (int) $item['id'];
        $tabelaOrigem = $item['tabela_origem'];
        $tentativas = (int) ($item['qualidade_tentativas'] ?? 0);
        $tipoEtiquetaJob = "IDENTIFICACAO_Q{$tentativas}";
        if (jaFoiImpressa($stmtJaImpressa, $idItem, $tabelaOrigem, $tipoEtiquetaJob)) {
            continue;
        }
        $jobs[] = [
            'id' => "etq:{$tabelaOrigem}:{$idItem}:{$tipoEtiquetaJob}",
            'type' => 'print',
            'payload' => [
                'printer' => 'Zebra_ZD230',
                'text' => montarZplIdentificacao($item),
                'copies' => 1,
            ],
        ];
    }
}

echo json_encode(['jobs' => $jobs]);
