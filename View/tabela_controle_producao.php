<?php
require_once '../Function/trava.php'; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Pedidos</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; color: #333; margin: 25px; }
        .header-painel { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h1 { color: #2c3e50; margin: 0; font-size: 28px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th { background-color: #2c3e50; color: white; padding: 15px; text-align: center; font-size: 16px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eef2f5; font-size: 16px; text-align: center; vertical-align: middle; }
        tr:hover { background-color: #f8fafc; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 14px; font-weight: bold; display: inline-block; }
        .status-pendente { background-color: #ffeeba; color: #856404; border: 1px solid #ffeeba; }
        .status-em-andamento { background-color: #b8daff; color: #004085; border: 1px solid #b8daff; }
        .status-concluido { background-color: #c3e6cb; color: #155724; border: 1px solid #c3e6cb; }
        
        .badge-origem { padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .origem-producao { background-color: #e2e3e5; color: #383d41; }
        .origem-os { background-color: #f8d7da; color: #721c24; }

        .sem-pedidos { text-align: center; padding: 50px; color: #7f8c8d; font-size: 18px; font-weight: 500; }
        .footer { margin-top: 20px; margin-bottom: 20px; font-size: 0.85rem; color: #bdc3c7; }
    </style>
</head>
<body>
    <div class="header-painel">
        <h1>Painel de Controle da Produção</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Tipo</th>
                <th>Prazo de Produção</th>
                <th>Andamento da Produção</th>
                <th>Status Produção</th>
            </tr>
        </thead>
        <tbody>
            <?php
            require_once '../config/Database.php'; 
            require_once '../Model/Sistema.php'; 

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);
            $pedidos = $sistema->mostrarFilaProducao(); 

            if (!empty($pedidos)): 
                foreach($pedidos as $p): 
                    $total = $p['total_itens'];
                    $concluidos = $p['itens_concluidos'];
                    $numPedido = $p['numero_pedido'];
                    $origem = $p['origem']; 

                    $idUnicoLinha = $numPedido . '-' . $origem;

                    if ($concluidos == 0) {
                        $statusProducao = "Pendente";
                        $classeClasse   = "status-pendente";
                    } elseif ($concluidos > 0 && $concluidos < $total) {
                        $statusProducao = "Em produção";
                        $classeClasse   = "status-em-andamento";
                    } else {
                        $statusProducao = "Produzido";
                        $classeClasse   = "status-concluido";
                    }

                    $statusPosProducao = ($statusProducao === "Produzido") ? "Pronto para Embalagem" : "Aguardando";
                    $classeOrigem = ($origem === 'OS') ? 'origem-os' : 'origem-producao';
            ?>
                <tr id="Linha-<?= $idUnicoLinha ?>" data-id="<?= $idUnicoLinha ?>">
                    <td style="font-weight: bold; color: #2980b9;">
                        <?= htmlspecialchars($numPedido ?? '') ?>
                    </td>
                    
                    <td>
                        <span class="badge-origem <?= $classeOrigem ?>">
                            <?= htmlspecialchars($origem) ?>
                        </span>
                    </td>
                    
                    <td>
                        <?php 
                        if (!empty($p['prazo_producao'])) {
                            echo htmlspecialchars(substr($p['prazo_producao'], 0, 10));
                        } else {
                            echo "Sem prazo";
                        }
                        ?>
                    </td>
                    
                    <td>
                        <span class="badge <?= $classeClasse ?>">
                            <?= $statusProducao ?> (<?= $concluidos ?>/<?= $total ?>)
                        </span>
                    </td>
                    
                    <td>
                        <span class="badge">
                            <?= $statusPosProducao ?>
                        </span>
                    </td>
                </tr>
            <?php 
                endforeach; 
            else: 
            ?>
                <tr>
                    <td colspan="5" class="sem-pedidos">Nenhum pedido em produção encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    let pedidosAtuais = Array.from(document.querySelectorAll('tbody tr[id^="pedido-"]'))
                             .map(tr => tr.id.replace('pedido-', ''));

    function verificarAtualizacoesEmSegundoPlano() {
        if (document.activeElement && document.activeElement.tagName === 'INPUT') {
            return; 
        }

        fetch('../Function/dados_tabelas.php?tela=pos_venda')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const novosDados = data.dados;
                    
                    const novosIds = novosDados.map(p => p.numero_pedido.toString());

                    const temNovoItem = novosIds.some(id => !pedidosAtuais.includes(id));
                    const itemSumiu = pedidosAtuais.some(id => !novosIds.includes(id));

                    if (temNovoItem || itemSumiu) {
                        window.location.reload();
                    }
                }
            })
            .catch(err => console.error("Erro na sincronização:", err));
    }

    setInterval(verificarAtualizacoesEmSegundoPlano, 7000);
    </script>
    
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>
</html>