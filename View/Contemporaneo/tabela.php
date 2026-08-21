<?php
require_once '../../Function/trava.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itens</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 10px;
            max-width: 100vw;
            height: calc(100vh - 20px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }


        .table-container {
            width: 100%;
            height: calc(100vh - 100px);
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            overflow-x: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid black;
            border-radius: 20px;
            background: #fff;
        }

        .sem-pedidos {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-size: 18px;
            font-weight: 500;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            background: #fff;
        }

        th {
            background-color: #ffffff;
            height: 50px;
            color: black;
            padding: 10px 5px;
            text-transform: uppercase;
            font-size: 18px;
            word-wrap: break-word;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: inset 0 -2px 0 #1c1c1c;
        }

        td {
            padding: 8px 4px;
            border: 1px solid #1c1c1c;
            text-align: center;
            font-size: 18px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        td:first-child,
        th:first-child {
            font-weight: bold;
            color: blue;
            width: 80px;
            font-size: 20px;
        }

        .linha-resumo-gaiola td {
            position: -webkit-sticky;
            position: sticky;
            bottom: 0;
            z-index: 10;
            background-color: #ffffff;
            text-align: left;
            font-size: 18px;
            box-shadow: inset 0 2px 0 #1c1c1c;
        }

        .column-data {
            font-weight: bold;
            font-size: 20px;
            color: #bb4242;
        }

        .numero-pedido.misto {
            display: inline-block;
            background: #8e44ad;
            color: #fff;
            padding: 2px 8px;
            border-radius: 6px;
        }

        input#input-pistola {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            opacity: 0;
            border: none;
            padding: 0;
            margin: 0;
            pointer-events: none;
            z-index: -1;
        }

        #feedback-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: table;
            text-align: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        #feedback-box.active {
            opacity: 1;
        }

        #feedback-content {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            color: white;
            font-size: 45px;
            font-weight: bold;
            padding: 20px;
        }

        #feedback-content .sub-item {
            font-size: 45px;
            margin-top: 25px;
            background: rgba(0, 0, 0, 0.2);
            display: inline-block;
            padding: 15px 40px;
            border-radius: 50px;
        }

        .footer {
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #bdc3c7;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <input type="text" id="input-pistola" autofocus>

    <div class="table-container">
        <table>
            <?php
            require_once '../../config/Database.php';
            require_once '../../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();

            $sistema = new Sistema($db);
            $pedidosMistos = $sistema->pedidosMistos('itens_producao');

            $arquivo_cache = __DIR__ . '/../../cache/dados_painel.json';
            $tempo_expiracao = 30;

            if (file_exists($arquivo_cache) && (time() - filemtime($arquivo_cache) < $tempo_expiracao)) {
                $dados_tabela = json_decode(file_get_contents($arquivo_cache), true);
            } else {
                $dados_tabela = $sistema->mostrarTabela();

                if (!is_dir(__DIR__ . '/../../cache')) {
                    mkdir(__DIR__ . '/../../cache', 0777, true);
                }
                file_put_contents($arquivo_cache, json_encode($dados_tabela, JSON_UNESCAPED_UNICODE));
            }

            $pedidos = !empty($dados_tabela) ? $dados_tabela : [];
            $pedidos_agrupados = $pedidos;
            ?>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Prazo</th>
                    <th>Reformer</th>
                    <th>Carrinho (Ref)</th>
                    <th>Torre</th>
                    <th>Carrinho (Tor)</th>
                    <th>Cadilac</th>
                    <th>Step</th>
                    <th>Barrel</th>
                    <th>Wall Unit</th>
                    <th>Acessórios</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $equipamentos = [
                    'Reformer Excellence',
                    'Carrinho Excellence',
                    'Reformer Torre',
                    'Carrinho Torre',
                    'Cadilac Excelence',
                    'Step Chair Excelence',
                    'Lader Barrel Excelence',
                    'Wall Unit',
                ];

                // Gaiola Cadilac não é mais um item por pedido: é um total
                // agregado (planejado da semana x real já embalado), igual em
                // toda a tabela (ver Sistema::contarGaiolasCadilacProducao).
                $gaiolasProducao = $sistema->contarGaiolasCadilacProducao('itens_producao');

                $lista_acessorios = [
                    'Caixa Mini',
                    'Caixa do Reformer',
                    'P. de Molas - B R I N D E',
                    'P. de Molas - C O M P L E T A',
                    'P. de Molas - P u s h T h r u',
                    'Caixa da Cadeira',
                    'Prancha de Alongamento',
                    'SPINE CORRECTOR',
                    'SMALL BARREL',
                    'BASTÃO ALUMÍNIO 1,5 M',
                    'PUSH UP DEVICE (PAR)',
                ];
                $placeholders_acessorios = implode(',', array_fill(0, count($lista_acessorios), '?'));
                ?>

                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="11" class="sem-pedidos">Nenhum item em produção pendente na fábrica.</td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($pedidos_agrupados as $pedido): ?>
                        <tr id="linha-<?= htmlspecialchars($pedido['numero']) ?>">
                            <td>
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <span class="numero-pedido<?= in_array($pedido['numero'], $pedidosMistos) ? ' misto' : '' ?>" <?= in_array($pedido['numero'], $pedidosMistos) ? 'title="Pedido misto: tem itens da linha Contemporânea e da Clássica"' : '' ?>><?= htmlspecialchars($pedido['numero']) ?></span>
                                </div>
                            </td>

                            <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 10)) ?></td>

                            <?php foreach ($equipamentos as $nome_equipamento):
                                $stmt = $db->prepare("SELECT id, status FROM itens_producao WHERE numero_pedido = ? AND equipamento = ? AND numero_pedido NOT LIKE 'OS%'");
                                $stmt->execute([$pedido['numero'], $nome_equipamento]);
                                $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <td>
                                    <div style="display: flex; justify-content: center;">
                                        <?php if ($pecas && count($pecas) > 0):
                                            foreach ($pecas as $peca):
                                                $status = isset($peca['status']) ? $peca['status'] : 'Em Produção';
                                                $id_peca = isset($peca['id']) ? $peca['id'] : 0;

                                                $texto = '❌';
                                                $estilo = '';

                                                if ($peca['status'] === 'Produzido') {
                                                    $texto = '✅';
                                                } elseif ($peca['status'] === 'Embalado' || $peca['status'] === 'Armazenado') {
                                                    $texto = 'E';
                                                    $estilo = 'style="color: #27ae60; font-weight: bold; font-size: 30px;"';
                                                }
                                        ?>
                                                <span class="item-check"
                                                    data-id="<?= $peca['id'] ?>"
                                                    data-pedido="<?= htmlspecialchars($pedido['numero']) ?>"
                                                    data-equipamento="<?= htmlspecialchars($nome_equipamento) ?>"
                                                    <?= $estilo ?>
                                                    style="font-size: 24px;">
                                                    <?= $texto ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #ccc;">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <?php
                                $sqlAcess = "SELECT status FROM itens_producao WHERE numero_pedido = ? AND equipamento IN ($placeholders_acessorios)";
                                $stmtAcess = $db->prepare($sqlAcess);
                                $paramsAcess = array_merge([$pedido['numero']], $lista_acessorios);
                                $stmtAcess->execute($paramsAcess);
                                $status_acessorios = $stmtAcess->fetchAll(PDO::FETCH_COLUMN);

                                if (count($status_acessorios) === 0) {
                                    echo '<span style="color: #ccc; font-size: 20px;">-</span>';
                                } else {
                                    $totalAcess = count($status_acessorios);
                                    $totalEmbalados = 0;
                                    $totalFinalizados = 0;

                                    foreach ($status_acessorios as $st) {
                                        if ($st === 'Embalado' || $st === 'Armazenado') $totalEmbalados++;
                                        if ($st === 'Produzido') $totalFinalizados++;
                                    }

                                    if ($totalEmbalados === $totalAcess) {
                                        echo '<span class="item-check status-acessorio-coletivo" data-pedido="' . htmlspecialchars($pedido['numero']) . '" data-equipamento="Acessórios" style="color: #27ae60; font-weight: bold; font-size: 30px;">E</span>';
                                    } elseif (($totalEmbalados + $totalFinalizados) === $totalAcess) {
                                        echo '<span class="item-check status-acessorio-coletivo" data-pedido="' . htmlspecialchars($pedido['numero']) . '" data-equipamento="Acessórios" style="font-size: 25px;">✅</span>';
                                    } else {
                                        echo '<span class="item-check status-acessorio-coletivo" data-pedido="' . htmlspecialchars($pedido['numero']) . '" data-equipamento="Acessórios" style="font-size: 25px;">❌</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="linha-resumo-gaiola">
                    <td colspan="11">Gaiola Cadilac da semana — Planejado: <strong><?= $gaiolasProducao['planejado'] ?></strong> &nbsp;|&nbsp; Real: <strong><?= $gaiolasProducao['real'] ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="feedback-box">
        <div id="feedback-content"></div>
    </div>

    <script>
        let pedidosAtuais = Array.from(document.querySelectorAll('tbody tr[id^="linha-"]'))
            .map(tr => tr.id.replace('linha-', ''));

        function verificarAtualizacoesRapidas() {
            fetch('../../Function/dados_tabelas.php?tela=expedicao_producao')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosDados = data.dados;

                        const novosPedidos = novosDados.map(p => {
                            const valorPedido = p.numero_pedido || p.numero || '';
                            return valorPedido.toString();
                        });

                        const temNovoItem = novosPedidos.some(num => !pedidosAtuais.includes(num));
                        const itemSumiu = pedidosAtuais.some(num => !novosPedidos.includes(num));

                        if (temNovoItem || itemSumiu) {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => console.error("Erro na sincronização rápida:", err));
        }

        setInterval(verificarAtualizacoesRapidas, 30000);

        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tempoRefresh = urlParams.get('refresh') || null;
            if (tempoRefresh) {
                setTimeout(() => {
                    window.location.href = `index.php?&refresh=${tempoRefresh}`;
                }, tempoRefresh * 500);
            }
        })();

        const inputPistola = document.getElementById('input-pistola');

        document.addEventListener('click', () => inputPistola.focus({
            preventScroll: true
        }));
        window.onload = () => inputPistola.focus({
            preventScroll: true
        });

        inputPistola.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const idLido = this.value;
                this.value = '';

                if (idLido) {
                    atualizarStatusNoBanco(idLido);
                }
            }
        });

        async function atualizarStatusNoBanco(codigoCompleto) {
            try {
                const response = await fetch(`../../Function/atualizar_etapa.php?id=${codigoCompleto}&origem=producao`);
                const data = await response.json();

                if (!data.success) {
                    console.error("Erro no servidor:", data.error);
                    alert("Erro: " + data.error);
                    return;
                }

                const icon = document.querySelector(`.item-check[data-id="${data.idReal}"]`);
                const nrPedido = data.pedidoReal || "Desconhecido";
                const nmItem = data.equipamentoReal || "Equipamento";

                if (icon) {
                    if (data.statusGerado === 'Produzido') {
                        icon.innerText = '✅';
                        icon.style.color = '';
                        icon.style.fontWeight = 'normal';
                        icon.style.fontSize = '20px';
                    } else if (data.statusGerado === 'Embalado') {
                        icon.innerText = 'E';
                        icon.style.color = '#27ae60';
                        icon.style.fontWeight = 'bold';
                        icon.style.fontSize = '30px';
                    }
                }

                dispararFeedbackCerto(nrPedido, nmItem, data.statusGerado);

                setTimeout(async () => {
                    if (icon) {
                        await verificarLinha(icon.closest('tr'));
                    }

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                }, 3000);

            } catch (err) {
                console.error("Erro na requisição:", err);
            }
        }

        function verificarLinha(linha) {
            return new Promise((resolve) => {
                if (!linha) return resolve();

                const itensLista = linha.querySelectorAll('.item-check');
                if (itensLista.length === 0) return resolve();

                const pendentes = Array.from(itensLista).filter(i => i.innerText.trim() !== 'E');

                if (pendentes.length > 0) return resolve();

                const numeroPedido = linha.id.replace('linha-', '');

                fetch(`../../Function/notificar_posVenda.php?pedido=${encodeURIComponent(numeroPedido)}&tipo_tela=os_equipamentos`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.success) {
                            const celulas = linha.querySelectorAll('td');
                            linha.style.transition = "background-color 0.6s ease, opacity 0.8s ease";
                            celulas.forEach(td => td.style.transition = "background-color 0.6s ease");

                            if (data.status_pedido === 'SUBIU_POS_VENDA') {
                                linha.style.backgroundColor = "#d4edda";
                                celulas.forEach(td => td.style.backgroundColor = "#d4edda");

                                setTimeout(() => {
                                    linha.style.opacity = "0";

                                    setTimeout(() => {
                                        linha.remove();
                                        resolve();
                                    }, 800);

                                }, 800);

                            } else {
                                linha.style.backgroundColor = "#ffeaa7";
                                celulas.forEach(td => td.style.backgroundColor = "#ffeaa7");
                                setTimeout(resolve, 800);
                            }
                        } else {
                            console.error("O banco recusou a inserção:", data.error);
                            resolve();
                        }
                    })
                    .catch(err => {
                        console.error("Falha na comunicação com o servidor:", err);
                        resolve();
                    });
            });
        }

        document.querySelectorAll('tbody tr').forEach(tr => verificarLinha(tr));

        function dispararFeedbackCerto(pedido, item, status) {
            const box = document.getElementById('feedback-box');
            const content = document.getElementById('feedback-content');

            if (!box || !content) return;

            box.style.backgroundColor = (status === 'Embalado') ?
                'rgba(39, 174, 96, 0.85)' :
                'rgba(46, 196, 182, 0.85)';

            content.innerHTML = `<div>PEDIDO: <strong>#${pedido}</strong></div>
                                 <div class="sub-item">${item} &rarr; <u>${status.toUpperCase()}</u></div>`;

            box.classList.add('active');

            setTimeout(() => {
                box.classList.remove('active');
            }, 3000);
        }
    </script>
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>