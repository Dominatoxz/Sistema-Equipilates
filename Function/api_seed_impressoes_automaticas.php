<?php
// Script de uso único: marca TODOS os itens pendentes atuais como já
// impressos (sem imprimir nada de verdade), pra servir de linha de base
// antes de ligar a impressão automática. Depois de rodado, este arquivo
// deve ser removido do servidor.

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

$db = (new Database())->getConnection();

$stmtUsuario = $db->prepare("SELECT id FROM usuarios WHERE usuario = 'sistema_automatico' LIMIT 1");
$stmtUsuario->execute();
$usuarioAutoId = $stmtUsuario->fetchColumn();

if (!$usuarioAutoId) {
    echo json_encode(['success' => false, 'error' => "Usuário 'sistema_automatico' não existe na tabela usuarios."]);
    exit;
}

$sql = "
    SELECT id, equipamento, 'PRODUCAO' AS tabela_origem
    FROM itens_producao
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
    UNION ALL
    SELECT id, equipamento, 'OS' AS tabela_origem
    FROM itens_os
    WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'
";
$itens = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$stmtCheck = $db->prepare(
    "SELECT 1 FROM impressoes_etiquetas WHERE id_item = :id_item AND tabela_origem = :tabela_origem AND tipo_etiqueta = :tipo_etiqueta LIMIT 1"
);
$stmtIns = $db->prepare("INSERT INTO impressoes_etiquetas
    (id_item, tabela_origem, tipo_etiqueta, usuario_id, usuario_nome, motivo_reimpressao)
    VALUES (:id_item, :tabela_origem, :tipo_etiqueta, :usuario_id, :usuario_nome, 'Linha de base - acumulo anterior a automacao')");

$db->beginTransaction();
$marcados = 0;
foreach ($itens as $item) {
    $idItem = (int) $item['id'];
    $tabelaOrigem = $item['tabela_origem'];
    $nomeEquipamento = trim($item['equipamento']);
    $apenasEmbalagem = strcasecmp($nomeEquipamento, 'Carrinho') === 0
        || strcasecmp($nomeEquipamento, 'Gaiola') === 0
        || strcasecmp($nomeEquipamento, 'Gaiola Cadilac') === 0;

    $tipos = $apenasEmbalagem ? ['EMBALAGEM'] : ['PRODUCAO', 'EMBALAGEM'];

    foreach ($tipos as $tipo) {
        $stmtCheck->execute([':id_item' => $idItem, ':tabela_origem' => $tabelaOrigem, ':tipo_etiqueta' => $tipo]);
        if ($stmtCheck->fetchColumn()) {
            continue;
        }
        $stmtIns->execute([
            ':id_item' => $idItem,
            ':tabela_origem' => $tabelaOrigem,
            ':tipo_etiqueta' => $tipo,
            ':usuario_id' => $usuarioAutoId,
            ':usuario_nome' => 'Impressão Automática',
        ]);
        $marcados++;
    }
}
$db->commit();

echo json_encode(['success' => true, 'marcados' => $marcados]);
