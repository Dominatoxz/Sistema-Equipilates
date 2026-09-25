<?php
require_once '../../Function/trava.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armazenagem</title>
    <style>
        :root {
            --bg-gradient: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            --panel-bg: #ffffff;
            --border-tech: rgba(15, 23, 42, 0.06);
            --tech-blue: #2563eb;
            --tech-green: #10b981;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            margin: 0;
            padding: 24px 30px 16px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
            flex-shrink: 0;
        }

        .header-painel h1 {
            margin: 0;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-painel .subtitulo {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .btn-modo-antigo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid var(--border-tech);
            padding: 8px 16px;
            border-radius: 30px;
            background: var(--panel-bg);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-modo-antigo:hover {
            border-color: var(--tech-blue);
            color: var(--tech-blue);
        }

        .filtros-armazenagem {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            flex-shrink: 0;
        }

        .chip-filtro {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--panel-bg);
            border: 1px solid var(--border-tech);
            border-radius: 30px;
            padding: 7px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-secondary);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }

        .chip-filtro:hover {
            border-color: var(--tech-blue);
        }

        .chip-filtro.ativo {
            background: var(--tech-blue);
            border-color: var(--tech-blue);
            color: #fff;
        }

        .chip-filtro .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .chip-filtro .num {
            font-variant-numeric: tabular-nums;
        }

        .dot-total {
            background: var(--tech-blue);
        }

        .dot-atraso {
            background: #dc2626;
        }

        .dot-ok {
            background: #059669;
        }

        .dot-embalado {
            background: #2563eb;
        }

        .table-container {
            width: 100%;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: auto;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.03);
            border: 1px solid var(--border-tech);
            border-radius: 14px;
            background: var(--panel-bg);
        }

        .sem-pedidos {
            text-align: center;
            padding: 60px;
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--panel-bg);
        }

        th {
            background-color: #0f172a;
            color: #ffffff;
            height: auto;
            padding: 14px 16px;
            text-align: center;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            font-weight: 700;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
            text-align: center;
            font-size: 0.95rem;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background-color: #f8fafc;
        }

        tbody tr.oculto-filtro {
            display: none;
        }

        td:first-child,
        th:first-child {
            font-weight: 800;
            width: 100px;
        }

        td:first-child {
            color: var(--tech-blue);
            font-size: 1.05rem;
        }

        .column-data {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .numero-pedido.misto {
            display: inline-block;
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .item-check {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .item-check[data-status="Armazenado"] {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #6ee7b7;
        }

        .badge-atraso {
            display: inline-block;
            margin-left: 6px;
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            border: 1px solid #fca5a5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-ok {
            display: inline-block;
            margin-left: 6px;
            background-color: #ecfdf5;
            color: #065f46;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            border: 1px solid #a7f3d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            text-align: center;
            flex-shrink: 0;
        }

        /* Ajuste responsivo básico pra tela de celular/tablet no chão de fábrica. */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header-painel {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .header-painel h1 {
                font-size: 1.2rem;
            }

            th {
                padding: 10px 8px;
                font-size: 0.65rem;
            }

            td {
                font-size: 0.8rem;
                padding: 10px 8px;
            }
        }
    </style>
</head>

<body>
    <input type="text" id="input-pistola" autofocus>

    <div class="header-painel">
        <div>
            <h1>📦 Armazenagem</h1>
            <div class="subtitulo">Itens embalados aguardando conferência</div>
        </div>
        <a href="central_expedicao_antigo.php" class="btn-modo-antigo">Forma antiga</a>
    </div>

    <?php
    require_once '../../config/Database.php';
    require_once '../../Model/Sistema.php';

    $database = new Database();
    $db = $database->getConnection();
    $sistema = new Sistema($db);

    $pedidosMistos = $sistema->pedidosMistos('itens_producao');

    // Tela unica de Armazenagem (2026-09-16): antes eram 4 telas
    // separadas (Equip./Acess. de Contemporaneo e Classico), cada
    // uma com sua grade de colunas por equipamento. Aqui juntamos os
    // 4 conjuntos de pedidos elegiveis (mesmo criterio de sempre: ao
    // menos 1 item Embalado/Armazenado e nem tudo armazenado ainda,
    // ver Sistema::mostrarTabelaExpedicao*) e mostramos cada ITEM em
    // uma linha só, ordenado do mais atrasado pro mais em dia — a
    // logica de bipagem (input-pistola -> atualizar_etapa.php) e a
    // notificacao automatica pro pos-venda quando o pedido fica 100%
    // armazenado continuam exatamente as mesmas de antes.
    $pedidosPorNumero = [];
    foreach ([
        $sistema->mostrarTabelaExpedicaoContemporaneo(),
        $sistema->mostrarTabelaExpedicaoContemporaneoAcessorios(),
        $sistema->mostrarTabelaExpedicaoClassico(),
        $sistema->mostrarTabelaExpedicaoClassicoAcessorios(),
    ] as $listaPedidos) {
        foreach ($listaPedidos as $p) {
            if (!isset($pedidosPorNumero[$p['numero']])) {
                $pedidosPorNumero[$p['numero']] = $p;
            }
        }
    }

    $linhasItens = [];
    $hoje = new DateTime('today');

    foreach ($pedidosPorNumero as $pedido) {
        $isOsPedido = (stripos($pedido['numero'], 'os') !== false);
        $tabelaItensPedido = $isOsPedido ? 'itens_os' : 'itens_producao';
        $prefixoIdPedido = $isOsPedido ? 'OS' : '';
        $condicaoOs = $isOsPedido ? "numero_pedido LIKE 'OS%'" : "numero_pedido NOT LIKE 'OS%'";

        // Só entra na lista o ITEM que já foi bipado até Embalado — e some
        // sozinho da tela assim que for bipado pra Armazenado (Matheus,
        // 2026-09-16: "quando o item for armazenado, ele some da tela").
        $stmtItens = $db->prepare("SELECT id, equipamento, status, prazo_producao FROM $tabelaItensPedido WHERE numero_pedido = ? AND equipamento NOT LIKE 'Emb.%' AND status = 'Embalado' AND $condicaoOs ORDER BY posicao_no_pedido ASC");
        $stmtItens->execute([$pedido['numero']]);
        $itensPedido = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

        foreach ($itensPedido as $item) {
            $prazoData = DateTime::createFromFormat('d/m/Y', substr($item['prazo_producao'], 0, 10));
            $diasAtraso = $prazoData ? (int) round(($hoje->getTimestamp() - $prazoData->getTimestamp()) / 86400) : 0;

            $linhasItens[] = [
                'id_completo' => $prefixoIdPedido . $item['id'],
                'numero_pedido' => $pedido['numero'],
                'prazo' => $prazoData ? $prazoData->format('d/m') : substr($item['prazo_producao'], 0, 5),
                'item' => $item['equipamento'],
                'status' => trim((string) $item['status']),
                'dias_atraso' => $diasAtraso,
            ];
        }
    }

    usort($linhasItens, fn($a, $b) => $b['dias_atraso'] <=> $a['dias_atraso']);

    $totalItens = count($linhasItens);
    $totalAtrasados = count(array_filter($linhasItens, fn($l) => $l['dias_atraso'] > 0));
    $totalOk = $totalItens - $totalAtrasados;
    $totalEmbalados = count(array_filter($linhasItens, fn($l) => $l['status'] === 'Embalado'));
    ?>

    <div class="filtros-armazenagem">
        <div class="chip-filtro ativo" data-filtro="todos"><span class="dot dot-total"></span>Total <span class="num" id="contagem-todos"><?= $totalItens ?></span></div>
        <div class="chip-filtro" data-filtro="atraso"><span class="dot dot-atraso"></span>Atrasados <span class="num" id="contagem-atraso"><?= $totalAtrasados ?></span></div>
        <div class="chip-filtro" data-filtro="ok"><span class="dot dot-ok"></span>OK <span class="num" id="contagem-ok"><?= $totalOk ?></span></div>
        <div class="chip-filtro" data-filtro="embalado"><span class="dot dot-embalado"></span>Embalados <span class="num" id="contagem-embalado"><?= $totalEmbalados ?></span></div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Prazo</th>
                    <th>Item</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="corpo-tabela">
                <?php if (empty($linhasItens)): ?>
                    <tr>
                        <td colspan="4" class="sem-pedidos">Nenhum item aguardando armazenagem.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($linhasItens as $l): ?>
                        <tr data-pedido-num="<?= htmlspecialchars($l['numero_pedido']) ?>" data-atrasado="<?= $l['dias_atraso'] > 0 ? '1' : '0' ?>" data-item-status="<?= htmlspecialchars($l['status']) ?>">
                            <td>
                                <span class="numero-pedido<?= in_array($l['numero_pedido'], $pedidosMistos) ? ' misto' : '' ?>" <?= in_array($l['numero_pedido'], $pedidosMistos) ? 'title="Pedido misto: tem itens da linha Contemporânea e da Clássica"' : '' ?>>
                                    <?= htmlspecialchars($l['numero_pedido']) ?>
                                </span>
                            </td>
                            <td class="column-data"><?= htmlspecialchars($l['prazo']) ?></td>
                            <td style="text-align:left; white-space:normal;"><?= htmlspecialchars($l['item']) ?></td>
                            <td>
                                <span class="item-check"
                                    data-id="<?= htmlspecialchars($l['id_completo']) ?>"
                                    data-pedido="<?= htmlspecialchars($l['numero_pedido']) ?>"
                                    data-equipamento="<?= htmlspecialchars($l['item']) ?>"
                                    data-status="<?= $l['status'] ?>">
                                    <?= $l['status'] ?>
                                </span>
                                <?php if ($l['dias_atraso'] > 0): ?>
                                    <span class="badge-atraso">Atraso (<?= $l['dias_atraso'] ?>d)</span>
                                <?php else: ?>
                                    <span class="badge-ok">OK</span>
                                <?php endif; ?>
                            </td>
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
        let pedidosAtuais = Array.from(new Set(
            Array.from(document.querySelectorAll('tbody tr[data-pedido-num]')).map(tr => tr.dataset.pedidoNum)
        ));

        // --- Filtros com contagem (Total / Atrasados / OK / Embalados) ---
        let filtroAtivo = 'todos';

        function linhasDeItem() {
            return Array.from(document.querySelectorAll('tbody tr[data-pedido-num]'));
        }

        function atualizarContagens() {
            const linhas = linhasDeItem();
            const total = linhas.length;
            const atraso = linhas.filter(l => l.dataset.atrasado === '1').length;
            const ok = total - atraso;
            const embalado = linhas.filter(l => l.dataset.itemStatus === 'Embalado').length;

            document.getElementById('contagem-todos').textContent = total;
            document.getElementById('contagem-atraso').textContent = atraso;
            document.getElementById('contagem-ok').textContent = ok;
            document.getElementById('contagem-embalado').textContent = embalado;
        }

        function aplicarFiltro() {
            linhasDeItem().forEach(linha => {
                let mostra = true;
                if (filtroAtivo === 'atraso') mostra = linha.dataset.atrasado === '1';
                else if (filtroAtivo === 'ok') mostra = linha.dataset.atrasado === '0';
                else if (filtroAtivo === 'embalado') mostra = linha.dataset.itemStatus === 'Embalado';
                linha.classList.toggle('oculto-filtro', !mostra);
            });
        }

        document.querySelectorAll('.chip-filtro').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.chip-filtro').forEach(c => c.classList.remove('ativo'));
                chip.classList.add('ativo');
                filtroAtivo = chip.dataset.filtro;
                aplicarFiltro();
            });
        });

        function verificarAtualizacoesRapidas() {
            fetch('../../Function/dados_tabelas.php?tela=armazenagem_unificada')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosPedidos = data.dados.map(p => (p.numero_pedido || p.numero || '').toString());

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
        let bipagemBloqueada = false;
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

                if (bipagemBloqueada) return;

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

                    bipagemBloqueada = true;
                    await dispararFeedbackCerto(nrPedido, nmItem, data.statusGerado);
                    bipagemBloqueada = false;

                    // Item virou Armazenado: some da tela na hora, sem recarregar a
                    // pagina inteira (Matheus, 2026-09-16: "quando o item for
                    // armazenado, ele some da tela"). O aviso pro pos-venda
                    // (subir pra fila do Financeiro) ja acontece sozinho no
                    // servidor, dentro de atualizar_etapa.php, toda vez que um
                    // item vira Embalado/Armazenado — nao precisa (e nao deve)
                    // ser checado de novo aqui: isso e o que causava item
                    // ainda so Embalado sumindo da tela sem motivo (Matheus,
                    // 2026-09-16: "uns itens que ficam sumindo" ao recarregar).
                    if (data.statusGerado === 'Armazenado' && icon) {
                        const linha = icon.closest('tr');
                        if (linha) {
                            linha.style.transition = "opacity 0.5s, background 0.4s";
                            linha.style.background = "#d1fae5";
                            linha.style.opacity = "0";
                            setTimeout(() => {
                                linha.remove();
                                atualizarContagens();
                            }, 450);
                        }
                    }
                } else {
                    console.error("Erro no servidor:", data.error);
                    bipagemBloqueada = true;
                    await dispararFeedbackErro(data.error);
                    bipagemBloqueada = false;
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

        function dispararFeedbackErro(mensagem) {
            return new Promise((resolve) => {
                const box = document.getElementById('feedback-box');
                const content = document.getElementById('feedback-content');

                if (!box || !content) return resolve();

                box.style.backgroundColor = 'rgba(192, 57, 43, 0.92)';
                content.innerHTML = `<div class="sub-item">${mensagem}</div>`;

                box.classList.add('active');

                setTimeout(() => {
                    box.classList.remove('active');
                    setTimeout(resolve, 400);
                }, 3000);
            });
        }

    </script>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>
