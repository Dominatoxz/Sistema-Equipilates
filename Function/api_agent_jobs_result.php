<?php
/**
 * Adapter pro agente eqagent: recebe o resultado de 1 job de impressão
 * (api.jobs_result_path — configurado como caminho FIXO, sem "{id}" nele;
 * o poller faz path_tpl.format(id=...) mas se não houver placeholder ele
 * simplesmente ignora o id e usa o caminho como está, então isso é válido)
 * e, se status=ok, confirma em impressoes_etiquetas — igual
 * api_confirmar_impressao_automatica.php faz, só que a partir do formato de
 * resultado do agente em vez do formato antigo {"itens":[...]}.
 *
 * O agente NÃO reenvia o payload original no resultado (só id/type/status/
 * result/timestamps) — por isso api_agent_jobs_next.php codifica
 * tabela_origem/id_item/tipo_etiqueta dentro do próprio "id" do job
 * ("etq:<tabela_origem>:<id_item>:<tipo_etiqueta>"), decodificado aqui.
 *
 * Criado em 2026-09-09, ver api_agent_jobs_next.php.
 */
require_once '../global.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$tokenEsperado = getenv('AUTO_PRINT_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_AUTO_TOKEN'] ?? '';

if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$id = (string) ($dados['id'] ?? '');
$status = (string) ($dados['status'] ?? '');

$partes = explode(':', $id, 4);
if (count($partes) !== 4 || $partes[0] !== 'etq') {
    echo json_encode(['success' => false, 'error' => 'id de job não reconhecido: ' . $id]);
    exit;
}
[, $tabelaOrigem, $idItemStr, $tipoEtiqueta] = $partes;
$idItem = (int) $idItemStr;

if ($status !== 'ok') {
    // Job falhou no agente (impressora offline, papel acabou, etc.) — NÃO
    // confirma. O item continua aparecendo em api_agent_jobs_next.php até
    // imprimir de verdade (retry automático pelo próprio poller). Só loga
    // pra dar pra investigar se ficar se repetindo sempre.
    error_log('api_agent_jobs_result: job ' . $id . ' reportou status=' . $status . ': ' . json_encode($dados['result'] ?? $dados['error'] ?? $dados));
    echo json_encode(['success' => true, 'confirmado' => false]);
    exit;
}

try {
    $db = (new Database())->getConnection();

    $stmtUsuario = $db->prepare("SELECT id FROM usuarios WHERE usuario = 'sistema_automatico' LIMIT 1");
    $stmtUsuario->execute();
    $usuarioAutoId = $stmtUsuario->fetchColumn();

    if (!$usuarioAutoId) {
        echo json_encode(['success' => false, 'error' => "Usuário 'sistema_automatico' não existe na tabela usuarios."]);
        exit;
    }

    $stmtCheck = $db->prepare(
        "SELECT 1 FROM impressoes_etiquetas WHERE id_item = :id_item AND tabela_origem = :tabela_origem AND tipo_etiqueta = :tipo_etiqueta LIMIT 1"
    );
    $stmtCheck->execute([':id_item' => $idItem, ':tabela_origem' => $tabelaOrigem, ':tipo_etiqueta' => $tipoEtiqueta]);
    if ($stmtCheck->fetchColumn()) {
        echo json_encode(['success' => true, 'confirmado' => false, 'motivo' => 'já estava confirmado']);
        exit;
    }

    $db->prepare("INSERT INTO impressoes_etiquetas
        (id_item, tabela_origem, tipo_etiqueta, usuario_id, usuario_nome, motivo_reimpressao)
        VALUES (:id_item, :tabela_origem, :tipo_etiqueta, :usuario_id, :usuario_nome, NULL)")
       ->execute([
           ':id_item'       => $idItem,
           ':tabela_origem' => $tabelaOrigem,
           ':tipo_etiqueta' => $tipoEtiqueta,
           ':usuario_id'    => $usuarioAutoId,
           ':usuario_nome'  => 'Impressão Automática (agente)',
       ]);

    echo json_encode(['success' => true, 'confirmado' => true]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
