<?php
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

if (!isset($dados['itens']) || !is_array($dados['itens']) || empty($dados['itens'])) {
    echo json_encode(['success' => false, 'error' => 'Nenhuma etiqueta informada.']);
    exit;
}

$itens = [];
foreach ($dados['itens'] as $it) {
    if (!isset($it['id_item'], $it['tabela_origem'], $it['tipo_etiqueta'])) {
        continue;
    }
    $itens[] = [
        'id_item'       => (int) $it['id_item'],
        'tabela_origem' => trim($it['tabela_origem']),
        'tipo_etiqueta' => trim($it['tipo_etiqueta']),
    ];
}

if (empty($itens)) {
    echo json_encode(['success' => false, 'error' => 'Nenhuma etiqueta válida informada.']);
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

    $stmtIns = $db->prepare("INSERT INTO impressoes_etiquetas
        (id_item, tabela_origem, tipo_etiqueta, usuario_id, usuario_nome, motivo_reimpressao)
        VALUES (:id_item, :tabela_origem, :tipo_etiqueta, :usuario_id, :usuario_nome, NULL)");

    $db->beginTransaction();
    $confirmados = 0;
    foreach ($itens as $it) {
        $stmtCheck->execute([
            ':id_item' => $it['id_item'],
            ':tabela_origem' => $it['tabela_origem'],
            ':tipo_etiqueta' => $it['tipo_etiqueta'],
        ]);
        if ($stmtCheck->fetchColumn()) {
            continue; 
        }

        $stmtIns->execute([
            ':id_item'       => $it['id_item'],
            ':tabela_origem' => $it['tabela_origem'],
            ':tipo_etiqueta' => $it['tipo_etiqueta'],
            ':usuario_id'    => $usuarioAutoId,
            ':usuario_nome'  => 'Impressão Automática',
        ]);
        $confirmados++;
    }
    $db->commit();

    echo json_encode(['success' => true, 'confirmados' => $confirmados]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
