<?php
require_once '../../Function/trava.php';
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
            overflow-x: hidden;
        }

        .table-container {
            width: 100%;
            height: calc(100vh - 20px);
            max-height: calc(100vh - 20px);
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
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        th {
            background-color: #ffffff;
            height: 300px;
            color: black;
            padding: 8px 4px;
            text-transform: uppercase;
            font-size: 13px;
            white-space: nowrap;
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
            border: 1px solid #1c1c1c;
            box-shadow: inset 0 -2px 0 #1c1c1c;
        }

        /* Pedido e Prazo continuam na horizontal — só os nomes de
           equipamento/acessório (a maioria das colunas) ficam verticais. */
        th:first-child,
        th:nth-child(2) {
            writing-mode: horizontal-tb;
            transform: none;
            white-space: normal;
        }

        td {
            padding: 8px 4px;
            border: 1px solid #1c1c1c;
            text-align: center;
            font-size: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        td:first-child,
        th:first-child {
            font-weight: bold;
            color: blue;
            width: 60px;
            font-size: 16px;
        }

        .column-data {
            font-weight: bold;
            font-size: 18px;
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
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
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

            $arquivo_cache = __DIR__ . '/../../cache/dados_painel_classico_acess.json';
            $tempo_expiracao = 30;

            if (file_exists($arquivo_cache) && (time() - filemtime($arquivo_cache) < $tempo_expiracao)) {
                $dados_tabela = json_decode(file_get_contents($arquivo_cache), true);
            } else {
                $dados_tabela = $sistema->mostrarTabelaClassicoAcessorios();

                if (!is_dir(__DIR__ . '/../../cache')) {
                    mkdir(__DIR__ . '/../../cache', 0777, true);
                }
                file_put_contents($arquivo_cache, json_encode($dados_tabela, JSON_UNESCAPED_UNICODE));
            }

            $pedidos = !empty($dados_tabela) ? $dados_tabela : [];
            $pedidos_agrupados = $pedidos;

            $equipamentos = [
                'CAIXA DO REFORMER CLÁSSICA'         => 'Caixa Reformer',
                'SUPORTE SPINE CORRECTOR'             => 'Suporte Spine',
                'MINI EXTENSÃO MOVE FLOW'             => 'Mini Extensão',
                'PLATAFORMA BARREL CLÁSSICO'          => 'Plataforma Barrel',
                'BARRA PUSH TRUE (BALANÇO CLASSICO)'  => 'Barra PT',
                'SPACER BOX'                          => 'Spacer Box',
                '2 x 4 (TWO BY FOUR)'                 => '2 X 4',
                'KUNA BOARD'                          => 'Kuna Board',
                'TRAVESSEIRO BENCH MAT'               => 'Travesseiro (BM)',
                'TRAVESSEIRO RÉGUA'                   => 'Travesseiro (R)',
                'TRAVESSEIRO 1/2 LUA'                 => 'Travesseiro (Lua)',
                'TRAV. CILINDRICO'                    => 'Trav Cilindrico',
                'TRAV. OMBREIRA (PAR)'                => 'Trav Ombreira',
                'TRAV. CABEC. 30 mm'                  => 'Trav Cabec 30',
                'TRAV. CABEC. 40 mm'                  => 'Trav Cabec 40',
                'CAPA PROT. BARREL CLÁSS.'            => 'Capa Prot Barrel',
                'SHEEPSKIN COVER'                      => 'Sheepskin',
                'PUXADOR DE ALUMINIO'                  => 'Puxador Aluminio',
                'ANEL DE PILATES ARCHIVE AÇO'          => 'Anel de Pilates',
                'MAGIC SQUARE'                          => 'Magic Square',
                'FOOT CORREC. ALUM.'                   => 'Foot Correc',
                'BEAN BAG'                              => 'Bean Bag',
                'BREATH A CIZER'                        => 'Breath a Cizer',
                'NECK STRETCHER'                         => 'Neck Stretcher',
                'HAND TENS O METER'                     => 'Hand Tens',
                'TOE EXERCISER'                          => 'Toe Exerciser',
                'AIR PLANE BOARD'                        => 'Air Plane',
                'FINGER EXERCISE'                        => 'Finger Exercise',
                'MINI BARREL'                            => 'Mini Barrel',
                'MINI SPINE'                              => 'Mini Spine',
            ];

            $itensPorPedido = [];
            $equipamentosComDados = [];

            $numerosPedidos = array_column($pedidos_agrupados, 'numero');
            if (!empty($numerosPedidos)) {
                $nomesEquipamentos = array_keys($equipamentos);
                $placeholdersPedidos = implode(',', array_fill(0, count($numerosPedidos), '?'));
                $placeholdersEquip = implode(',', array_fill(0, count($nomesEquipamentos), '?'));

                $sqlLote = "SELECT id, status, status_qualidade, qualidade_tentativas, numero_pedido, equipamento
                            FROM itens_producao
                            WHERE numero_pedido IN ($placeholdersPedidos)
                              AND equipamento IN ($placeholdersEquip)
                              AND numero_pedido NOT LIKE 'OS%'";
                $stmtLote = $db->prepare($sqlLote);
                $stmtLote->execute(array_merge($numerosPedidos, $nomesEquipamentos));

                foreach ($stmtLote->fetchAll(PDO::FETCH_ASSOC) as $peca) {
                    $itensPorPedido[$peca['numero_pedido']][$peca['equipamento']][] = $peca;
                    $equipamentosComDados[$peca['equipamento']] = true;
                }
            }

            $equipamentosVisiveis = array_filter(
                $equipamentos,
                fn($nome) => isset($equipamentosComDados[$nome]),
                ARRAY_FILTER_USE_KEY
            );

            $totalColunas = 2 + count($equipamentosVisiveis);
            ?>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Prazo</th>
                    <?php foreach ($equipamentosVisiveis as $rotulo): ?>
                        <th><?= htmlspecialchars($rotulo) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="<?= $totalColunas ?>" class="sem-pedidos">Nenhum item em produção pendente na fábrica.</td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($pedidos_agrupados as $pedido): ?>
                        <tr id="linha-<?= htmlspecialchars($pedido['numero']) ?>">
                            <td><?= htmlspecialchars($pedido['numero']) ?></td>

                            <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 5)) ?></td>

                            <?php foreach ($equipamentosVisiveis as $nome_equipamento => $rotulo):
                                $pecas = $itensPorPedido[$pedido['numero']][$nome_equipamento] ?? [];
                            ?>
                                <td>
                                    <div style="display: flex; justify-content: center;">
                                        <?php if (!empty($pecas)):
                                            foreach ($pecas as $peca):
                                                $texto = '❌';
                                                $estilo = '';
                                                $seloQ = '';

                                                if ($peca['status'] === 'Pendente' && ((int) ($peca['qualidade_tentativas'] ?? 0)) > 0) {
                                                    $texto = '⚠️';
                                                } elseif ($peca['status'] === 'Produzido') {
                                                    $texto = '✅';
                                                    if (!in_array($peca['status_qualidade'] ?? 'N/A', ['N/A', ''], true)) {
                                                        $corQ = ($peca['status_qualidade'] ?? '') === 'Aprovado' ? '#2a7a4f' : '#a9700f';
                                                        $seloQ = ' <span title="Qualidade" style="display:inline-block;background:' . $corQ . ';color:#fff;font-size:10px;font-weight:bold;border-radius:50%;width:14px;height:14px;line-height:14px;text-align:center;vertical-align:top;">Q</span>';
                                                    }
                                                } elseif ($peca['status'] === 'Embalado' || $peca['status'] === 'Armazenado') {
                                                    $texto = 'E';
                                                    $estilo = 'style="color: #27ae60; font-weight: bold; font-size: 28px;"';
                                                }
                                        ?>
                                                <span class="item-check"
                                                    data-id="<?= $peca['id'] ?>"
                                                    <?= $estilo ?>
                                                    style="font-size: 22px;">
                                                    <?= $texto . $seloQ ?>
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
            fetch('../../Function/dados_tabelas.php?tela=producao_acess_classico')
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

        setInterval(verificarAtualizacoesRapidas, 100000);

        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tempoRefresh = urlParams.get('refresh') || null;
            if (tempoRefresh) {
                setTimeout(() => {
                    window.location.href = `index.php?refresh=${tempoRefresh}`;
                }, tempoRefresh * 1000);
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
                        icon.style.fontSize = '';
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
                    }, 500);

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

                fetch(`../../Function/notificar_posVenda_classico.php?pedido=${encodeURIComponent(numeroPedido)}&tipo_tela=os_equipamentos`)
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