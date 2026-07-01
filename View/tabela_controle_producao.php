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

        .badge-atrasado { 
            background-color: #e2606d; 
            color: white; 
            padding: 5px 12px; 
            border-radius: 4px; 
            font-weight: bold; 
            display: inline-block;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        .tooltip-itens {
            display: none;
            position: absolute;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 12px;
            border-radius: 8px;
            z-index: 9999;
            min-width: 280px;
            max-width: 400px;
            
            max-height: 250px; 
            overflow-y: auto;
        }
        .tooltip-itens h4 { margin: 0 0 8px 0; color: #2c3e50; font-size: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; position: sticky; top: 0; background: #fff; }
        .tooltip-itens ul { margin: 0; padding: 0; list-style: none; }
        .tooltip-itens li { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 6px 0; 
            font-size: 13px; 
            border-bottom: 1px dashed #f1f5f9; 
        }
        .tooltip-itens li:last-child { border-bottom: none; }
        .badge-andamento { cursor: pointer; }
    </style>
</head>
<body>
    <div class="header-painel">
        <h1>Painel de Controle da Production</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Tipo</th>
                <th>Prazo de Produção</th>
                <th>Andamento da Production</th>
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
                        $statusProducao = "Embalado"; 
                        $classeClasse   = "status-concluido";
                    }

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
                            $prazoStr = substr(trim($p['prazo_producao']), 0, 10);
                            
                            date_default_timezone_set('America/Sao_Paulo');
                            $hoje = new DateTime('today'); 
                            
                            $prazoData = null;
                            if (strpos($prazoStr, '/') !== false) {
                                $prazoData = DateTime::createFromFormat('d/m/Y', $prazoStr);
                            } else {
                                $prazoData = DateTime::createFromFormat('Y-m-d', $prazoStr);
                            }
                            
                            if ($prazoData) {
                                $prazoData->setTime(0, 0, 0); 
                                
                                if ($prazoData < $hoje && $statusProducao !== "Embalado") {
                                    echo '<span class="badge-atrasado">' . $prazoData->format('d/m/Y') . '</span>';
                                } else {
                                    echo $prazoData->format('d/m/Y'); 
                                }
                            } else {
                                echo htmlspecialchars($prazoStr); 
                            }
                        } else {
                            echo "Sem prazo";
                        }
                        ?>
                    </td>
                    
                    <td>
                        <span class="badge <?= $classeClasse ?> badge-andamento" 
                              data-pedido="<?= htmlspecialchars($numPedido) ?>" 
                              data-origem="<?= htmlspecialchars($origem) ?>">
                            <?= $statusProducao ?> (<?= $concluidos ?>/<?= $total ?>)
                        </span>
                    </td>
                </tr>
            <?php 
                endforeach; 
            else: 
            ?>
                <tr>
                    <td colspan="4" class="sem-pedidos">Nenhum pedido em produção encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="tooltip-itens-pedido" class="tooltip-itens">
        <h4>Itens do Pedido</h4>
        <ul id="conteudo-tooltip-itens"></ul>
    </div>

    <script>
    let pedidosAtuais = Array.from(document.querySelectorAll('tbody tr[data-id]'))
                             .map(tr => tr.getAttribute('data-id').split('-')[0]);

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

    setInterval(verificarAtualizacoesEmSegundoPlano, 10000);

    const tooltip = document.getElementById('tooltip-itens-pedido');
    const conteudoTooltip = document.getElementById('conteudo-tooltip-itens');
    let abortController = null;
    let tempoEsperaFechar = null; 

    function abrirTooltip(badge) {
        clearTimeout(tempoEsperaFechar); 

        const pedido = badge.getAttribute('data-pedido');
        const origem = badge.getAttribute('data-origem');

        const rect = badge.getBoundingClientRect();
        tooltip.style.top = (rect.bottom + window.scrollY + 4) + 'px';
        tooltip.style.left = (rect.left + window.scrollX) + 'px';
        tooltip.style.display = 'block';

        if (tooltip.getAttribute('data-aberto-agora') === pedido + '-' + origem) {
            return;
        }
        tooltip.setAttribute('data-aberto-agora', pedido + '-' + origem);

        conteudoTooltip.innerHTML = '<li style="color:#7f8c8d;">Carregando itens...</li>';

        if (abortController) abortController.abort();
        abortController = new AbortController();

        fetch(`../Function/buscar_itens_popup.php?pedido=${pedido}&origem=${origem}`, { signal: abortController.signal })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.itens.length > 0) {
                    conteudoTooltip.innerHTML = data.itens.map(item => {
                        let corStatus = '#ffeeba; color: #856404;'; 
                        if(item.status === 'Em produção' || item.status === 'Produzindo') corStatus = '#b8daff; color: #004085;';
                        if(item.status === 'Embalado' || item.status === 'Produzido') corStatus = '#c3e6cb; color: #155724;';

                        return `<li>
                            <span style="font-weight:600; color:#34495e; padding-right: 15px;">${item.nome}</span>
                            <span style="font-size:11px; padding:2px 8px; border-radius:12px; font-weight:bold; background:${corStatus}; white-space:nowrap;">${item.status}</span>
                        </li>`;
                    }).join('');
                } else {
                    conteudoTooltip.innerHTML = '<li style="color:#e74c3c;">Nenhum detalhe encontrado.</li>';
                }
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    conteudoTooltip.innerHTML = '<li style="color:#e74c3c;">Erro ao carregar lista.</li>';
                }
            });
    }

    function agendarFechamento() {
        tempoEsperaFechar = setTimeout(() => {
            tooltip.style.display = 'none';
            tooltip.removeAttribute('data-aberto-agora');
            if (abortController) abortController.abort();
        }, 300); 
    }

    document.addEventListener('mouseover', function(e) {
        const badge = e.target.closest('.badge-andamento');
        if (badge) abrirTooltip(badge);
    });

    document.addEventListener('mouseout', function(e) {
        const badge = e.target.closest('.badge-andamento');
        if (badge) agendarFechamento();
    });

    tooltip.addEventListener('mouseenter', () => clearTimeout(tempoEsperaFechar));
    tooltip.addEventListener('mouseleave', agendarFechamento);
    </script>
    
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>
</html>