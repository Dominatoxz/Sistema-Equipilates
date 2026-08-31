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
            $pedidosMistos = array_merge($sistema->pedidosMistos('itens_producao'), $sistema->pedidosMistos('itens_os'));

            $arquivo_cache = __DIR__ . '/../../cache/dados_painel_expedicao_classico.json';
            $tempo_expiracao = 30;

            if (file_exists($arquivo_cache) && (time() - filemtime($arquivo_cache) < $tempo_expiracao)) {
                $dados_tabela = json_decode(file_get_contents($arquivo_cache), true);
            } else {
                $dados_tabela = $sistema->mostrarTabelaExpedicaoClassico();

                if (!is_dir(__DIR__ . '/../../cache')) {
                    mkdir(__DIR__ . '/../../cache', 0777, true);
                }
                file_put_contents($arquivo_cache, json_encode($dados_tabela, JSON_UNESCAPED_UNICODE));
            }

            $pedidos = !empty($dados_tabela) ? $dados_tabela : [];
            $pedidos_agrupados = $pedidos;

            $totalGaiolasPendentesArmazenagem = $sistema->contarGaiolasCadilacPendentesArmazenagem();

            $equipamentos = [
                'REF. CLASSICO ALUMINIO'      => 'Reformer Alumin',
                'CARRINHO CLASSICO'           => 'Carrinho (A)',
                'REF. CLASSICO TORRE'         => 'Reformer Torre',
                'CARRINHO CLASSICO TORRE'     => 'Carrinho (Tor)',
                'CAD. CLASSICO ALUMINIO'      => 'Cadilac Alumin',
                'GAIOLA CLASSICO'             => 'Gaiola (A)',
                'REF. CLASSICO TAUARI'        => 'Reformer Tauari',
                'CARRINHO CLASSICO TAUARI'    => 'Carrinho (T)',
                'CAD. CLASSICO TAUARI'        => 'Cadilac Tauari',
                'GAIOLA CADILCAC TAUARI'      => 'Gaiola (T)',
                'REFORMER HIBRIDO'            => 'Reformer Hibrido',
                'CARRINHO CLASSICO HIBRIDO'   => 'Carrinho (H)',
                'WUNDA CHAIR'                 => 'Wunda Chair',
                'ELECTRIC CHAIR'              => 'Eletric Chair',
                'ARM CHAIR'                   => 'Arm Chair',
                'LADDER BARREL CLÁSS.'        => 'Barrel',
                'PEDI O POLE'                 => 'POP',
                'WALL UNIT CLÁSSICO'          => 'Wall Unit',
                'MAT CLÁSSICO'                => 'Mat (C)',
                'MAT PORTÁTIL'                => 'Mat (P)',
                'BENCH MAT'                   => 'Bench',
                'GUILHOTINA'                  => 'Guilhotina',
            ];

            $lista_acessorios = [
                'CAIXA DO REFORMER CLÁSSICA',
                'SPINE CORRECTOR',
                'SMALL BARREL',
                'SUPORTE SPINE CORRECTOR',
                'MINI EXTENSÃO MOVE FLOW',
                'PLATAFORMA BARREL CLÁSSICO',
                'BARRA PUSH TRUE (BALANÇO CLASSICO)',
                'SPACER BOX',
                '2 x 4 (TWO BY FOUR)',
                'KUNA BOARD',
                'TRAVESSEIRO BENCH MAT',
                'TRAVESSEIRO RÉGUA',
                'TRAVESSEIRO 1/2 LUA',
                'FOOT CORREC. ALUM.',
                'BEAN BAG',
                'BREATH A CIZER',
                'NECK STRETCHER',
                'HAND TENS O METER',
                'TOE EXERCISER',
                'AIR PLANE BOARD',
                'FINGER EXERCISE',
                'PUSH UP DEVICE (PAR)',
                'MINI BARREL',
                'MINI SPINE',
                'TRAV. CILINDRICO',
                'TRAV. OMBREIRA (PAR)',
                'TRAV. CABEC. 30 mm',
                'TRAV. CABEC. 40 mm',
                'CAPA PROT. BARREL CLÁSS.',
                'SHEEPSKIN COVER',
                'BASTÃO ALUMÍNIO 1,5 M',
                'PUXADOR DE ALUMINIO',
                'ANEL DE PILATES ARCHIVE AÇO',
                'MAGIC SQUARE',
            ];
            $placeholders_acessorios = implode(',', array_fill(0, count($lista_acessorios), '?'));

            $itensPorPedido = [];
            $equipamentosComDados = [];

            $numerosPedidos = array_column($pedidos_agrupados, 'numero');
            if (!empty($numerosPedidos)) {
                $nomesEquipamentos = array_keys($equipamentos);
                $placeholdersPedidos = implode(',', array_fill(0, count($numerosPedidos), '?'));
                $placeholdersEquip = implode(',', array_fill(0, count($nomesEquipamentos), '?'));

                $lotes = [
                    ['tabela' => 'itens_producao', 'condicao' => "numero_pedido NOT LIKE 'OS%'", 'prefixo' => ''],
                    ['tabela' => 'itens_os', 'condicao' => "numero_pedido LIKE 'OS%'", 'prefixo' => 'OS'],
                ];

                foreach ($lotes as $lote) {
                    $sqlLote = "SELECT id, status, numero_pedido, equipamento
                                FROM {$lote['tabela']}
                                WHERE numero_pedido IN ($placeholdersPedidos)
                                  AND equipamento IN ($placeholdersEquip)
                                  AND {$lote['condicao']}";
                    $stmtLote = $db->prepare($sqlLote);
                    $stmtLote->execute(array_merge($numerosPedidos, $nomesEquipamentos));

                    foreach ($stmtLote->fetchAll(PDO::FETCH_ASSOC) as $peca) {
                        $peca['prefixo_id'] = $lote['prefixo'];
                        $itensPorPedido[$peca['numero_pedido']][$peca['equipamento']][] = $peca;
                        $equipamentosComDados[$peca['equipamento']] = true;
                    }
                }
            }

            $equipamentosVisiveis = array_filter(
                $equipamentos,
                fn($nome) => isset($equipamentosComDados[$nome]),
                ARRAY_FILTER_USE_KEY
            );

            $totalColunas = 2 + count($equipamentosVisiveis) + 1;
            ?>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Prazo</th>
                    <?php foreach ($equipamentosVisiveis as $rotulo): ?>
                        <th><?= htmlspecialchars($rotulo) ?></th>
                    <?php endforeach; ?>
                    <th>Acessórios</th>
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
                            <td>
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <span class="numero-pedido<?= in_array($pedido['numero'], $pedidosMistos) ? ' misto' : '' ?>" <?= in_array($pedido['numero'], $pedidosMistos) ? 'title="Pedido misto: tem itens da linha Contemporânea e da Clássica"' : '' ?>><?= htmlspecialchars($pedido['numero']) ?></span>
                                </div>
                            </td>

                            <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 10)) ?></td>

                            <?php foreach ($equipamentosVisiveis as $nome_equipamento => $rotulo):
                                $pecas = $itensPorPedido[$pedido['numero']][$nome_equipamento] ?? [];
                            ?>
                                <td>
                                    <div style="display: flex; justify-content: center;">
                                        <?php if (!empty($pecas)):
                                            foreach ($pecas as $peca):
                                                $texto = '❌';
                                                $estilo = '';

                                                if ($peca['status'] === 'Embalado') {
                                                    $texto = 'E';
                                                    $estilo = 'style="color: #2980b9; font-weight: bold; font-size: 28px;"';
                                                } elseif ($peca['status'] === 'Armazenado') {
                                                    $texto = 'A';
                                                    $estilo = 'style="color: #27ae60; font-weight: bold; font-size: 28px;"';
                                                }
                                        ?>
                                                <span class="item-check"
                                                    data-id="<?= ($peca['prefixo_id'] ?? '') . $peca['id'] ?>"
                                                    <?= $estilo ?>
                                                    style="font-size: 22px;">
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
                                $isOsPedidoAcess = (stripos($pedido['numero'], 'os') !== false);
                                $tabelaAcessPedido = $isOsPedidoAcess ? 'itens_os' : 'itens_producao';
                                $sqlAcess = "SELECT status FROM $tabelaAcessPedido WHERE numero_pedido = ? AND equipamento IN ($placeholders_acessorios)";
                                $stmtAcess = $db->prepare($sqlAcess);
                                $paramsAcess = array_merge([$pedido['numero']], $lista_acessorios);
                                $stmtAcess->execute($paramsAcess);
                                $status_acessorios = $stmtAcess->fetchAll(PDO::FETCH_COLUMN);

                                if (count($status_acessorios) === 0) {
                                    echo '<span style="color: #ccc; font-size: 18px;">-</span>';
                                } else {
                                    $totalAcess = count($status_acessorios);
                                    $totalArmazenados = 0;
                                    $totalEmbaladosOuMais = 0;

                                    foreach ($status_acessorios as $st) {
                                        if ($st === 'Armazenado') $totalArmazenados++;
                                        if ($st === 'Embalado' || $st === 'Armazenado') $totalEmbaladosOuMais++;
                                    }

                                    if ($totalArmazenados === $totalAcess) {
                                        echo '<span class="item-check status-acessorio-coletivo" style="color: #27ae60; font-weight: bold; font-size: 28px;">A</span>';
                                    } elseif ($totalEmbaladosOuMais === $totalAcess) {
                                        echo '<span class="item-check status-acessorio-coletivo" style="color: #2980b9; font-weight: bold; font-size: 28px;">E</span>';
                                    } else {
                                        echo '<span class="item-check status-acessorio-coletivo" style="font-size: 22px;">❌</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="linha-resumo-gaiola">
                    <td colspan="<?= $totalColunas ?>">Total de Gaiola Cadilac embalada aguardando armazenagem (todas as linhas): <strong><?= $totalGaiolasPendentesArmazenagem ?></strong></td>
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
                        const novosPedidos = novosDados.map(p => (p.numero_pedido || p.numero || '').toString());

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
                const idLido = this.value.trim();
                this.value = '';

                if (idLido) {
                    atualizarStatusNoBanco(idLido);
                }
            }
        });

        async function atualizarStatusNoBanco(codigoCompleto) {
            try {
                const response = await fetch(`../../Function/atualizar_etapa.php?id=${codigoCompleto}&origem=expedicao`);
                const data = await response.json();

                if (data.success) {
                    const icon = document.querySelector(`.item-check[data-id="${data.idReal}"]`);
                    let nrPedido = data.pedidoReal || "Desconhecido";
                    let nmItem = data.equipamentoReal || "Equipamento";

                    if (icon) {
                        if (data.statusGerado === 'Armazenado') {
                            icon.innerText = 'A';
                            icon.style.color = '#27ae60';
                            icon.style.fontWeight = 'bold';
                            icon.style.fontSize = '30px';
                        }
                    }

                    await dispararFeedbackCerto(nrPedido, nmItem, data.statusGerado);

                    if (icon) {
                        await verificarLinha(icon.closest('tr'));
                    }

                    window.location.reload();

                } else {
                    console.error("Erro no servidor:", data.error);
                    alert("Erro: " + data.error);
                }
            } catch (err) {
                console.error("Erro na requisição:", err);
            }
        }

        function dispararFeedbackCerto(pedido, item, status) {
            return new Promise((resolve) => {
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
                    setTimeout(resolve, 400);
                }, 3000);
            });
        }

        function verificarLinha(linha) {
            return new Promise((resolve) => {
                if (!linha) return resolve();

                const itensLista = linha.querySelectorAll('.item-check');
                if (itensLista.length === 0) return resolve();

                const pendentes = Array.from(itensLista).filter(i => i.innerText.trim() !== 'A');

                if (pendentes.length > 0) {
                    return resolve();
                }

                const numeroPedido = linha.id.replace('linha-', '');

                fetch(`../../Function/notificar_posVenda_classico.php?pedido=${encodeURIComponent(numeroPedido)}&tipo_tela=os_equipamentos`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.success) {
                            if (data.status_pedido === 'SUBIU_POS_VENDA') {
                                linha.style.transition = "opacity 0.8s, background 0.5s";
                                linha.style.background = "#d4edda";

                                setTimeout(() => {
                                    linha.remove();
                                    resolve();
                                }, 1000);
                            } else {
                                linha.style.transition = "background 0.5s";
                                linha.style.background = "#ffeaa7";
                                setTimeout(resolve, 500);
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
    </script>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>