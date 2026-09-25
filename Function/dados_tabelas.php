<?php
require_once '../Function/trava.php';
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
    } elseif ($tela === 'producao_acess') {
        $dados = $sistema->mostrarTabelaAcessorios();
    } elseif ($tela === 'producao_acess_os') {
        $dados = $sistema->mostrarTabelaAcessoriosOs();
    } elseif ($tela === 'producao_acess_os_classico') {
        $dados = $sistema->mostrarTabelaClassicoAcessoriosOs();
    } elseif ($tela === 'producao_classico') {
        $dados = $sistema->mostrarTabelaClassico();
    } elseif ($tela === 'producao_acess_classico') {
        $dados = $sistema->mostrarTabelaClassicoAcessorios();
    } elseif ($tela === 'expedicao_producao') {
        $dados = $sistema->mostrarTabelaExpedicaoContemporaneo();
    } elseif ($tela === 'expedicao_acess') {
        $dados = $sistema->mostrarTabelaExpedicaoContemporaneoAcessorios();
    } elseif ($tela === 'expedicao_classico') {
        $dados = $sistema->mostrarTabelaExpedicaoClassico();
    } elseif ($tela === 'expedicao_acess_classico') {
        $dados = $sistema->mostrarTabelaExpedicaoClassicoAcessorios();
    } elseif ($tela === 'armazenagem_unificada') {
        // Usado pela tela unica de Armazenagem (2026-09-16), que juntou as 4
        // telas antigas de Expedicao num so lugar — merge dos 4 conjuntos de
        // pedidos elegiveis, sem duplicar quem aparece em mais de uma
        // categoria.
        $porNumero = [];
        foreach ([
            $sistema->mostrarTabelaExpedicaoContemporaneo(),
            $sistema->mostrarTabelaExpedicaoContemporaneoAcessorios(),
            $sistema->mostrarTabelaExpedicaoClassico(),
            $sistema->mostrarTabelaExpedicaoClassicoAcessorios(),
        ] as $lista) {
            foreach ($lista as $p) {
                if (!isset($porNumero[$p['numero']])) {
                    $porNumero[$p['numero']] = $p;
                }
            }
        }
        $dados = array_values($porNumero);
    }

    echo json_encode(['success' => true, 'dados' => $dados]);
} catch (Exception $e) {
    error_log('dados_tabelas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar os dados. Tente novamente.']);
}
