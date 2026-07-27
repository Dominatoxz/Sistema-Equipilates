<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents("php://input"), true);

if (!isset($dados['itens']) || !is_array($dados['itens']) || empty($dados['itens'])) {
    echo json_encode(['success' => false, 'error' => 'Nenhuma etiqueta informada.']);
    exit;
}

$motivo = isset($dados['motivo']) ? trim($dados['motivo']) : '';

$itens = [];
foreach ($dados['itens'] as $it) {
    if (!isset($it['id_item'], $it['tabela_origem'], $it['tipo_etiqueta'])) {
        continue;
    }
    $itens[] = [
        'id_item'       => (int)$it['id_item'],
        'tabela_origem' => trim($it['tabela_origem']),
        'tipo_etiqueta' => trim($it['tipo_etiqueta']),
    ];
}

if (empty($itens)) {
    echo json_encode(['success' => false, 'error' => 'Nenhuma etiqueta válida informada.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $placeholders = [];
    $params = [];
    foreach ($itens as $i => $it) {
        $placeholders[] = "(:id_item{$i}, :origem{$i}, :tipo{$i})";
        $params[":id_item{$i}"] = $it['id_item'];
        $params[":origem{$i}"]  = $it['tabela_origem'];
        $params[":tipo{$i}"]    = $it['tipo_etiqueta'];
    }

    $sqlCheck = "SELECT id_item, tabela_origem, tipo_etiqueta FROM impressoes_etiquetas
                 WHERE (id_item, tabela_origem, tipo_etiqueta) IN (" . implode(',', $placeholders) . ")";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->execute($params);
    $jaImpressas = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

    $chavesImpressas = [];
    foreach ($jaImpressas as $row) {
        $chavesImpressas[$row['id_item'] . '|' . $row['tabela_origem'] . '|' . $row['tipo_etiqueta']] = true;
    }

    if (!empty($chavesImpressas) && $motivo === '') {
        echo json_encode([
            'success' => false,
            'precisa_motivo' => true,
            'qtd_repetidas' => count($chavesImpressas),
        ]);
        exit;
    }

    $usuarioId   = $_SESSION['usuario_id'];
    $usuarioNome = $_SESSION['usuario_logado'] ?? null;

    $stmtIns = $db->prepare("INSERT INTO impressoes_etiquetas
        (id_item, tabela_origem, tipo_etiqueta, usuario_id, usuario_nome, motivo_reimpressao)
        VALUES (:id_item, :tabela_origem, :tipo_etiqueta, :usuario_id, :usuario_nome, :motivo_reimpressao)");

    $db->beginTransaction();
    foreach ($itens as $it) {
        $chave = $it['id_item'] . '|' . $it['tabela_origem'] . '|' . $it['tipo_etiqueta'];
        $motivoLinha = isset($chavesImpressas[$chave]) ? $motivo : null;

        $stmtIns->execute([
            ':id_item'            => $it['id_item'],
            ':tabela_origem'      => $it['tabela_origem'],
            ':tipo_etiqueta'      => $it['tipo_etiqueta'],
            ':usuario_id'         => $usuarioId,
            ':usuario_nome'       => $usuarioNome,
            ':motivo_reimpressao' => $motivoLinha,
        ]);
    }
    $db->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
