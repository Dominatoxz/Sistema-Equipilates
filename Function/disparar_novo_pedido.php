<?php
require_once 'disparar_notificacao.php';
require_once '../Function/trava.php';
require_once '../Function/cargos.php';

$tela = $_GET['tela'] ?? '';

switch ($tela) {
    case 'financeiro':
        enviarNotificacaoPorSetor(
            CARGOS_FINANCEIRO_ACAO, 
            "Novo Pedido no Financeiro! 💰", 
            "Um novo pedido acabou de chegar na fila do Financeiro.",
            "../View/tabela_financeiro.php"
        );
        break;

    case 'posVenda':
        enviarNotificacaoPorSetor(
            CARGOS_POSVENDA_ACAO, 
            "Novo Pedido no Pós-Venda! 📦", 
            "Um novo pedido foi enviado para o setor de Pós-Venda.",
            "../View/tabela_posVenda.php"
        );
        break;

    case 'expedicao':
        enviarNotificacaoPorSetor(
            CARGOS_EXPEDICAO_ACAO, 
            "Novo Pedido na Expedição! 🚚", 
            "Um pedido novo chegou na expedição.",
            "../View/tabela_expedicao.php"
        );
        break;
}