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
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 25px;
        }

        .header-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eef2f5;
            font-size: 16px;
            text-align: center;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }

        .status-pendente {
            background-color: #ffeeba;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-em-andamento {
            background-color: #b8daff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .status-concluido {
            background-color: #c3e6cb;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-armazenado {
            background-color: #f0b8ff;
            color: #3a0242;
            border: 1px solid #f0b8ff;
        }

        .badge-origem {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .origem-producao {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .origem-os {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-origem.linha-misto {
            background-color: #ede9fe;
            color: #5b21b6;
        }

        .badge-origem.linha-contemporaneo {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-origem.linha-classico {
            background-color: #fef3c7;
            color: #92400e;
        }

        .sem-pedidos {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-size: 18px;
            font-weight: 500;
        }

        .footer {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
        }

        .badge-atrasado {
            background-color: #e2606d;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        .filtros-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .btn-filtro {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid #cbd5e1;
            background-color: #fff;
            color: #475569;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-filtro:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }

        .btn-filtro.active {
            background-color: #2c3e50;
            color: #fff;
            border-color: #2c3e50;
        }

        .btn-filtro span {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            min-width: 15px;
            text-align: center;
        }

        .btn-filtro.active span {
            outline: 1px solid rgba(255, 255, 255, 0.4);
        }

        .container-pesquisa {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .input-pesquisa {
            width: 100%;
            max-width: 400px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            border-radius: 6px;
            font-size: 14px;
            color: #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .input-pesquisa:focus {
            border-color: #2980b9;
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.15);
        }

        .tooltip-itens {
            display: none;
            position: absolute;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 12px;
            border-radius: 8px;
            z-index: 9999;
            min-width: 280px;
            max-width: 400px;
            max-height: 250px;
            overflow-y: auto;
        }

        .tooltip-itens h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            position: sticky;
            top: 0;
            background: #fff;
        }

        .tooltip-itens ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .tooltip-itens li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px dashed #f1f5f9;
        }

        .tooltip-itens li:last-child {
            border-bottom: none;
        }

        .badge-andamento {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="header-painel">
        <h1>Painel de Controle da Produção</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>

    <div class="filtros-container">
        <button class="btn-filtro active" data-filter="todos">
            Todos <span id="qtd-todos" style="background: #e2e8f0; color: #334155;">0</span>
        </button>
        <button class="btn-filtro" data-filter="atrasado">
            Atrasados <span id="qtd-atrasado" style="background: #e2606d; color: white;">0</span>
        </button>
        <button class="btn-filtro" data-filter="Pendente">
            Pendentes <span id="qtd-pendente" style="background: #ffeeba; color: #856404;">0</span>
        </button>
        <button class="btn-filtro" data-filter="Em produção">
            Em Produção <span id="qtd-producao" style="background: #b8daff; color: #004085;">0</span>
        </button>
        <button class="btn-filtro" data-filter="Embalado">
            Embalados <span id="qtd-embalado" style="background: #c3e6cb; color: #155724;">0</span>
        </button>
        <button class="btn-filtro" data-filter="Armazenado">
            Armazenados <span id="qtd-armazenado" style="background: #f0b8ff; color: #3a0242;">0</span>
        </button>
        <input type="text" id="inputPesquisa" class="input-pesquisa" placeholder="🔍 Buscar por Pedido / OS...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Tipo</th>
                <th>Linha</th>
                <th>Prazo de Produção</th>
                <th>Andamento da Produção</th>
            </tr>
        </thead>
        <tbody id="tabela-pedidos-body">
            <?php
            require_once '../config/Database.php';
            require_once '../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);
            $pedidos = $sistema->mostrarFilaProducao();

            if (!empty($pedidos)):
                foreach ($pedidos as $p):
                    $total = (int) ($p['total_itens'] ?? 0);
                    $concluidos = (int) ($p['itens_concluidos'] ?? 0);
                    $armazenados = (int) ($p['itens_armazenados'] ?? 0); 
                    
                    $totalFinalizados = $concluidos + $armazenados;

                    $numPedido = $p['numero_pedido'];
                    $origem = $p['origem'];
                    $statusBanco = $p['status'] ?? $p['status_pedido'] ?? '';

                    $idUnicoLinha = $numPedido . '-' . $origem;

                    if ($statusBanco === 'Armazenado' || ($armazenados >= $total && $total > 0)) {
                        $statusProducao = "Armazenado";
                        $classeClasse   = "status-armazenado";
                    } 
                    elseif ($totalFinalizados >= $total && $total > 0) {
                        $statusProducao = "Embalado";
                        $classeClasse   = "status-concluido";
                    } 
                    elseif ($totalFinalizados > 0 && $totalFinalizados < $total) {
                        $statusProducao = "Em produção";
                        $classeClasse   = "status-em-andamento";
                    } 
                    else {
                        $statusProducao = "Pendente";
                        $classeClasse   = "status-pendente";
                    }

                    $classeOrigem = ($origem === 'OS') ? 'origem-os' : 'origem-producao';

                    $linhaPedido = $sistema->linhaDoPedido($numPedido);
                    $classeLinha = match ($linhaPedido) {
                        'Misto' => 'linha-misto',
                        'Contemporâneo' => 'linha-contemporaneo',
                        'Clássico' => 'linha-classico',
                        default => '',
                    };
                    $textoLinha = $linhaPedido;
            ?>
                    <tr id="Linha-<?= $idUnicoLinha ?>" data-id="<?= $idUnicoLinha ?>" data-status="<?= $statusProducao ?>">
                        <td style="font-weight: bold; color: #2980b9;">
                            <?= htmlspecialchars($numPedido ?? '') ?>
                        </td>

                        <td>
                            <span class="badge-origem <?= $classeOrigem ?>">
                                <?= htmlspecialchars($origem) ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge-origem <?= $classeLinha ?>">
                                <?= htmlspecialchars($textoLinha) ?>
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

                                    if ($prazoData < $hoje && !in_array($statusProducao, ['Embalado', 'Armazenado'])) {
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
                                <?= $statusProducao ?> (<?= $totalFinalizados ?>/<?= $total ?>)
                            </span>
                        </td>
                    </tr>
                <?php
                endforeach;
            else:
                ?>
                <tr class="linha-sem-registro">
                    <td colspan="5" class="sem-pedidos">Nenhum pedido em produção encontrado.</td>
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

        setInterval(verificarAtualizacoesEmSegundoPlano, 60000);

        const botoesFiltro = document.querySelectorAll('.btn-filtro');
        const inputPesquisa = document.getElementById('inputPesquisa');
        const linhasTabela = document.querySelectorAll('#tabela-pedidos-body tr[data-id]');

        function atualizarContadores() {
            let todos = 0,
                atrasados = 0,
                pendentes = 0,
                producao = 0,
                embalados = 0,
                armazenados = 0;

            linhasTabela.forEach(tr => {
                todos++;
                const status = tr.getAttribute('data-status');
                const temBadgeAtrasado = tr.querySelector('.badge-atrasado') !== null;
                const estaFinalizado = (status === 'Embalado' || status === 'Armazenado');

                if (temBadgeAtrasado && !estaFinalizado) atrasados++;
                if (status === 'Pendente') pendentes++;
                if (status === 'Em produção') producao++;
                if (status === 'Embalado') embalados++;
                if (status === 'Armazenado') armazenados++;
            });

            document.getElementById('qtd-todos').textContent = todos;
            document.getElementById('qtd-atrasado').textContent = atrasados;
            document.getElementById('qtd-pendente').textContent = pendentes;
            document.getElementById('qtd-producao').textContent = producao;
            document.getElementById('qtd-embalado').textContent = embalados; 
            document.getElementById('qtd-armazenado').textContent = armazenados;
        }

        function aplicarFiltrosCombinados() {
            const botaoAtivo = document.querySelector('.btn-filtro.active');
            const filtroSelecionado = botaoAtivo.getAttribute('data-filter');
            const termoBusca = inputPesquisa.value.toLowerCase();

            let encontrouAlgum = false;

            linhasTabela.forEach(tr => {
                const statusLinha = tr.getAttribute('data-status');
                const temBadgeAtrasado = tr.querySelector('.badge-atrasado') !== null;
                const estaFinalizado = (statusLinha === 'Embalado' || statusLinha === 'Armazenado');
                const ehAtrasadoReal = temBadgeAtrasado && !estaFinalizado;

                const textoPedido = tr.getElementsByTagName('td')[0].textContent.toLowerCase();

                let atendeStatus = false;
                if (filtroSelecionado === 'todos') {
                    atendeStatus = true;
                } else if (filtroSelecionado === 'atrasado') {
                    atendeStatus = ehAtrasadoReal;
                } else {
                    atendeStatus = (statusLinha === filtroSelecionado);
                }

                const atendeBusca = textoPedido.includes(termoBusca);

                if (atendeStatus && atendeBusca) {
                    tr.style.display = '';
                    encontrouAlgum = true;
                } else {
                    tr.style.display = 'none';
                }
            });

            const avisoExistente = document.querySelector('.aviso-filtro-vazio');
            if (avisoExistente) avisoExistente.remove();

            if (!encontrouAlgum && linhasTabela.length > 0) {
                const tbody = document.getElementById('tabela-pedidos-body');
                const trAviso = document.createElement('tr');
                trAviso.className = 'aviso-filtro-vazio';
                trAviso.innerHTML = `<td colspan="5" class="sem-pedidos">Nenhum registro encontrado para a busca atual.</td>`;
                tbody.appendChild(trAviso);
            }
        }

        botoesFiltro.forEach(botao => {
            botao.addEventListener('click', function() {
                botoesFiltro.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                aplicarFiltrosCombinados();
            });
        });
        inputPesquisa.addEventListener('keyup', aplicarFiltrosCombinados);

        document.addEventListener("DOMContentLoaded", atualizarContadores);

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

            fetch(`../Function/buscar_itens_popup.php?pedido=${pedido}&origem=${origem}`, {
                    signal: abortController.signal
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.itens.length > 0) {
                        conteudoTooltip.innerHTML = data.itens.map(item => {
                            let corStatus = '#ffeeba; color: #856404;';
                            if (item.status === 'Em produção' || item.status === 'Produzindo') corStatus = '#b8daff; color: #004085;';
                            if (item.status === 'Embalado') corStatus = '#c3e6cb; color: #155724;';
                            if (item.status === 'Produzido') corStatus = '#b8daff; color: #004085;';
                            if (item.status === 'Armazenado') corStatus = '#f0b8ff; color: #3a0242;';

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