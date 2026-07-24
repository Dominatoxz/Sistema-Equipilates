<?php
require_once '../Function/trava.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessórios</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 10px;
            max-width: 100vw;
            min-height: calc(100vh - 20px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .table-container {
            width: 100%;
            height: calc(100vh - 120px);
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            overflow-x: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #1c1c1c;
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
            border-collapse: separate;
            border-spacing: 0;
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
            border-right: 1px solid #1c1c1c;
            border-bottom: 1px solid #1c1c1c;
            text-align: center;
            font-size: 22px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        td:last-child,
        th:last-child {
            border-right: none;
        }

        tr:last-child td {
            border-bottom: none;
        }

        td:first-child,
        th:first-child {
            font-weight: bold;
            color: blue;
            width: 60px;
            font-size: 22px;
        }

        .column-data {
            font-weight: bold;
            font-size: 20px;
            color: #bb4242;
        }

        .qr-link {
            background: #2ecc71;
            color: white;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 10px;
            text-decoration: none;
            font-weight: bold;
            display: block;
            margin: 2px 0;
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
            backdrop-filter: blur(5px);
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

        @keyframes pulseFlash {
            0% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <input type="text" id="input-pistola" autofocus>

    <div class="table-container">
        <table>
            <?php
            require_once '../config/Database.php';
            require_once '../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();

            $sistema = new Sistema($db);

            $arquivo_cache = __DIR__ . '/../cache/dados_painelAcess.json';
            $tempo_expiracao = 30;

            if (file_exists($arquivo_cache) && (time() - filemtime($arquivo_cache) < $tempo_expiracao)) {
                $dados_tabela = json_decode(file_get_contents($arquivo_cache), true);
            } else {
                $dados_tabela = $sistema->mostrarTabelaAcessorios();

                if (!is_dir(__DIR__ . '/../cache')) {
                    mkdir(__DIR__ . '/../cache', 0777, true);
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
                    <th>Caixa Mini</th>
                    <th>Caixa do Reformer</th>
                    <th>P. de Molas - B</th>
                    <th>P. de Molas - C</th>
                    <th>P. de Molas - PT</th>
                    <th>Caixa da Cadeira</th>
                    <th>Prancha de Alongamento</th>
                    <th>SPINE</th>
                    <th>SMALL</th>
                    <th>BASTÃO 1,5 M</th>
                    <th>PUSH UP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $equipamentos = [
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
                ?>

                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="9" class="sem-pedidos">Nenhum item em produção pendente na fábrica.</td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($pedidos_agrupados as $pedido): ?>
                        <tr id="linha-<?= htmlspecialchars($pedido['numero']) ?>">
                            <td class="num-column"><?= htmlspecialchars($pedido['numero']) ?></td>

                            <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 10)) ?></td>

                            <?php foreach ($equipamentos as $nome_equipamento):
                                $stmt = $db->prepare("SELECT id, status FROM itens_producao WHERE numero_pedido = ? AND equipamento = ?");
                                $stmt->execute([$pedido['numero'], $nome_equipamento]);
                                $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <td>
                                    <div style="display: flex; justify-content: center;">
                                        <?php if ($pecas && count($pecas) > 0):
                                            foreach ($pecas as $peca):
                                                $status = isset($peca['status']) ? $peca['status'] : 'Pendente';
                                                $id_peca = isset($peca['id']) ? $peca['id'] : 0;

                                                $texto = '❌';
                                                $estilo = '';

                                                if ($peca['status'] === 'Produzido') {
                                                    $texto = '✅';
                                                } elseif ($peca['status'] === 'Embalado') {
                                                    $texto = 'E';
                                                    $estilo = 'style="color: #27ae60; font-weight: bold; font-size: 30px;"';
                                                }
                                        ?>
                                                <span class="item-check"
                                                    data-id="<?= $peca['id'] ?>"
                                                    data-pedido="<?= htmlspecialchars($pedido['numero']) ?>"
                                                    data-equipamento="<?= htmlspecialchars($nome_equipamento) ?>"
                                                    <?= $estilo ?>
                                                    style="font-size: 20px;">
                                                    <?= $texto ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #ccc;">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
            fetch('../Function/dados_tabelas.php?tela=producao_acess')
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

        setInterval(verificarAtualizacoesRapidas, 20000);

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

        function atualizarStatusNoBanco(codigoCompleto) {
            fetch(`../Function/atualizar_etapa.php?id=${codigoCompleto}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = document.querySelector(`.item-check[data-id="${data.idReal}"]`);

                        let nrPedido = "Desconhecido";
                        let nmItem = "Equipamento";

                        if (icon) {
                            nrPedido = icon.getAttribute('data-pedido') || nrPedido;
                            nmItem = icon.getAttribute('data-equipamento') || nmItem;

                            if (data.statusGerado === 'Produzido') {
                                icon.innerText = '✅';
                                icon.style.color = '';
                                icon.style.fontWeight = 'normal';
                            } else if (data.statusGerado === 'Embalado') {
                                icon.innerText = 'E';
                                icon.style.color = '#27ae60';
                                icon.style.fontWeight = 'bold';
                                icon.style.fontSize = '30px';
                            }
                        } else {
                            const partes = codigoCompleto.split('-');
                            if (partes[0]) nrPedido = partes[0];
                        }

                        dispararFeedbackCerto(nrPedido, nmItem, data.statusGerado);

                        setTimeout(() => {
                            window.location.reload();
                        }, 5000);

                    } else {
                        console.error("Erro no servidor:", data.error);
                        alert("Erro: " + data.error);
                    }
                })
                .catch(err => console.error("Erro na requisição:", err));
        }

        function verificarLinha(linha) {
            const itensLista = linha.querySelectorAll('.item-check');
            if (itensLista.length === 0) return;

            const pendentes = Array.from(itensLista).filter(i => i.innerText.trim() !== 'E');

            if (pendentes.length === 0) {
                const numeroPedido = linha.cells[0].innerText.trim();

                fetch(`../Function/notificar_posVenda.php?pedido=${encodeURIComponent(numeroPedido)}&tipo_tela=os_equipamentos`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.success) {
                            if (data.status_pedido === 'SUBIU_POS_VENDA') {
                                linha.style.transition = "opacity 0.8s, background 0.5s";
                                linha.style.background = "#d4edda";
                                setTimeout(() => linha.remove(), 1000);
                            } else {
                                linha.style.transition = "background 0.5s";
                                linha.style.background = "#ffeaa7";
                            }
                        } else {
                            console.error("O banco recusou a inserção:", data.error);
                        }
                    })
                    .catch(err => console.error("Falha na comunicação com o servidor:", err));
            }
        }

        document.querySelectorAll('tbody tr').forEach(tr => verificarLinha(tr));

        function dispararFeedbackCerto(pedido, item, status) {
            const box = document.getElementById('feedback-box');
            const content = document.getElementById('feedback-content');

            if (status === 'Embalado') {
                box.style.backgroundColor = 'rgba(39, 174, 96, 0.85)';
            } else {
                box.style.backgroundColor = 'rgba(46, 196, 182, 0.85)';
            }

            content.innerHTML = `<div>PEDIDO: <strong>#${pedido}</strong></div>
                                 <div class="sub-item">${item} &rarr; <u>${status.toUpperCase()}</u></div>`;

            box.classList.add('active');

            setTimeout(() => {
                box.classList.remove('active');
            }, 3000);
        }

        let scrollSpeed = 0;

        function autoScroll() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                setTimeout(() => window.scrollTo(0, 0), 3000);
            } else {
                window.scrollBy(0, scrollSpeed);
            }
            requestAnimationFrame(autoScroll);
        }
        window.onload = () => {
            if (scrollSpeed > 0) autoScroll();
        };
    </script>
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>