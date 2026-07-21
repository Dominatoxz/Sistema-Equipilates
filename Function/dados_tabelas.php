<?php
require_once '../config/Database.php';
require_once '../Model/Sistema.php';

header('Content-Type: application/json');

$tela = $_GET['tela'] ?? 'producao';

try {
    $database = new Database();
    $db = $database->getConnection();
    $sistema = new Sistema($db);

    if ($tela === 'pos_venda') {
        $dados = $sistema->mostrarFilaPosVenda();
    } elseif ($tela === 'expedicao') {
        $dados = $sistema->mostrarFilaExpedicao();
    } elseif ($tela === 'financeiro') {
        $dados = $sistema->mostrarFilaFinanceiro();
    } elseif ($tela === 'producao') {
        $dados = $sistema->mostrarTabela();
    } elseif ($tela === 'producao_os') {
        $dados = $sistema->mostrarTabelaOs();
    } elseif ($tela === 'producao_acess') {
        $dados = $sistema->mostrarTabelaAcessorios();
    } elseif ($tela === 'producao_acess_os') {
        $dados = $sistema->mostrarTabelaAcessoriosOs();
    }

    echo json_encode(['success' => true, 'dados' => $dados]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
