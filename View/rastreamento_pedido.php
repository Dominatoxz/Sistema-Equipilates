<?php
require_once '../Function/trava.php';
require_once '../Function/cargos.php';

if (!isset($_SESSION['nivel_acesso']) || !in_array($_SESSION['nivel_acesso'], CARGOS_RASTREAMENTO)) {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreamento de Pedidos</title>
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-gradient: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            --panel-bg: #ffffff;
            --border-tech: rgba(15, 23, 42, 0.06);
            --tech-blue: #2563eb;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-main);
            background: var(--bg-gradient);
            color: var(--text-primary);
            margin: 0;
            padding: 30px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .header-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
        }

        h1 {
            color: var(--text-primary);
            margin: 0;
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
        }

        .btn-voltar {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid var(--border-tech);
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-voltar:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }

        .container-busca {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .input-pesquisa {
            flex: 1;
            min-width: 260px;
            max-width: 420px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .input-pesquisa:focus {
            border-color: var(--tech-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-busca-historico {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            display: none;
        }

        .btn-busca-historico:hover {
            background: #1e293b;
        }

        .aviso-escopo {
            width: 100%;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--panel-bg);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03), 0 1px 3px rgba(15, 23, 42, 0.02);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-tech);
        }

        th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 14px 10px;
            text-align: center;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 14px 10px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.04);
            font-size: 0.9rem;
            color: var(--text-primary);
            text-align: center;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr.linha-pedido:hover {
            background-color: #f8fafc;
        }

        .pedido-numero {
            font-weight: 800;
            color: var(--tech-blue);
            font-size: 1rem;
        }

        .badge-linha-misto {
            background-color: #f3e8ff;
            color: #6b21a8;
        }

        .badge-linha-contemporaneo {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-linha-classico {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-linha-indefinido {
            background-color: #f1f5f9;
            color: #475569;
        }

        .badge-linha-misto,
        .badge-linha-contemporaneo,
        .badge-linha-classico,
        .badge-linha-indefinido {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .estagio {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
        }

        .estagio-ausente {
            background-color: #f1f5f9;
            color: #94a3b8;
        }

        .estagio-em-andamento {
            background-color: #fef3c7;
            color: #92400e;
        }

        .estagio-ativo {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .estagio-concluido {
            background-color: #d1fae5;
            color: #065f46;
        }

        .sem-pedidos {
            text-align: center;
            padding: 60px;
            color: var(--text-secondary);
            font-size: 1.05rem;
            font-weight: 500;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header-painel">
        <h1>Rastreamento de Pedidos</h1>
        <a class="btn-voltar" href="../index.php">← Central</a>
    </div>

    <div class="container-busca">
        <input type="text" id="inputPesquisa" class="input-pesquisa" placeholder="🔍 Buscar por Pedido / OS...">
        <button type="button" id="btnBuscaHistorico" class="btn-busca-historico" onclick="buscarNoHistorico()">
            Buscar em todo o histórico
        </button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Linha</th>
                <th>Prazo</th>
                <th>Produção</th>
                <th>Armazenagem</th>
                <th>Financeiro</th>
                <th>Pós-venda</th>
                <th>Expedição</th>
            </tr>
        </thead>
        <tbody id="corpoTabela">
            <?php
            require_once '../config/Database.php';
            require_once '../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);

            $pedidos = $sistema->rastrearPedidos();
            ?>
            <?php if (empty($pedidos)): ?>
                <tr class="linha-sem-dados">
                    <td colspan="8" class="sem-pedidos">Nenhum pedido em andamento no momento.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p): ?>
                    <?= renderizarLinhaRastreamento($p) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    function renderizarLinhaRastreamento(array $p): string
    {
        $badgeLinhaClasse = match ($p['linha']) {
            'Misto' => 'badge-linha-misto',
            'Contemporâneo' => 'badge-linha-contemporaneo',
            'Clássico' => 'badge-linha-classico',
            default => 'badge-linha-indefinido',
        };

        $prod = $p['producao'];
        if ($prod['nao_iniciada']) {
            $badgeProducao = '<span class="estagio estagio-ausente">Sem itens</span>';
        } elseif ($prod['concluida']) {
            $badgeProducao = '<span class="estagio estagio-concluido">Concluída</span>';
        } else {
            $badgeProducao = '<span class="estagio estagio-em-andamento">Em produção (' . $prod['itens_embalados'] . '/' . $prod['total_itens'] . ')</span>';
        }

        $arm = $p['armazenagem'];
        if ($prod['nao_iniciada'] || $arm['itens_embalados'] === 0) {
            $badgeArmazenagem = '<span class="estagio estagio-ausente">—</span>';
        } elseif ($arm['concluida']) {
            $badgeArmazenagem = '<span class="estagio estagio-concluido">Concluída</span>';
        } else {
            $badgeArmazenagem = '<span class="estagio estagio-em-andamento">Pendente (' . $arm['itens_armazenados'] . '/' . $arm['total_itens'] . ')</span>';
        }

        $badgeFinanceiro = $p['financeiro']
            ? '<span class="estagio estagio-ativo">Na fila</span>'
            : '<span class="estagio estagio-ausente">—</span>';

        $badgePosVenda = $p['pos_venda']
            ? '<span class="estagio estagio-ativo">Na fila</span>'
            : '<span class="estagio estagio-ausente">—</span>';

        if ($p['finalizado']) {
            $badgeExpedicao = '<span class="estagio estagio-concluido">Finalizado</span>';
        } elseif ($p['expedicao_fila']) {
            $badgeExpedicao = '<span class="estagio estagio-ativo">Na fila</span>';
        } else {
            $badgeExpedicao = '<span class="estagio estagio-ausente">—</span>';
        }

        $prazo = !empty($p['prazo_producao']) ? htmlspecialchars(substr($p['prazo_producao'], 0, 10)) : '—';

        return '<tr class="linha-pedido" data-pedido="' . htmlspecialchars(strtolower($p['numero_pedido'])) . '">
            <td><span class="pedido-numero">' . htmlspecialchars($p['numero_pedido']) . '</span></td>
            <td><span class="' . $badgeLinhaClasse . '">' . htmlspecialchars($p['linha']) . '</span></td>
            <td>' . $prazo . '</td>
            <td>' . $badgeProducao . '</td>
            <td>' . $badgeArmazenagem . '</td>
            <td>' . $badgeFinanceiro . '</td>
            <td>' . $badgePosVenda . '</td>
            <td>' . $badgeExpedicao . '</td>
        </tr>';
    }
    ?>

    <script>
        const corpoTabela = document.getElementById('corpoTabela');
        const inputPesquisa = document.getElementById('inputPesquisa');
        const btnBuscaHistorico = document.getElementById('btnBuscaHistorico');

        function aplicarFiltro() {
            const termo = inputPesquisa.value.trim().toLowerCase();
            const linhas = corpoTabela.querySelectorAll('tr.linha-pedido');
            let algumVisivel = false;

            linhas.forEach(tr => {
                const bate = tr.getAttribute('data-pedido').includes(termo);
                tr.style.display = bate ? '' : 'none';
                if (bate) algumVisivel = true;
            });

            btnBuscaHistorico.style.display = (termo.length > 0 && !algumVisivel) ? 'inline-block' : 'none';
        }

        inputPesquisa.addEventListener('keyup', aplicarFiltro);

        function buscarNoHistorico() {
            const termo = inputPesquisa.value.trim();
            if (!termo) return;

            btnBuscaHistorico.disabled = true;
            btnBuscaHistorico.textContent = 'Buscando...';

            fetch(`../Function/rastrear_pedido.php?numero=${encodeURIComponent(termo)}`)
                .then(res => res.json())
                .then(data => {
                    btnBuscaHistorico.disabled = false;
                    btnBuscaHistorico.textContent = 'Buscar em todo o histórico';

                    document.querySelectorAll('.linha-sem-dados, .linha-busca-historico').forEach(tr => tr.remove());

                    if (!data.success || data.pedidos.length === 0) {
                        const tr = document.createElement('tr');
                        tr.className = 'linha-busca-historico';
                        tr.innerHTML = `<td colspan="8" class="sem-pedidos">Nenhum pedido encontrado com "${termo}" em todo o histórico.</td>`;
                        corpoTabela.appendChild(tr);
                        return;
                    }

                    data.pedidos.forEach(p => {
                        corpoTabela.insertAdjacentHTML('beforeend', montarLinhaHtml(p));
                    });

                    btnBuscaHistorico.style.display = 'none';
                })
                .catch(err => {
                    btnBuscaHistorico.disabled = false;
                    btnBuscaHistorico.textContent = 'Buscar em todo o histórico';
                    console.error('Erro ao buscar no histórico:', err);
                });
        }

        function montarLinhaHtml(p) {
            const classesLinha = {
                'Misto': 'badge-linha-misto',
                'Contemporâneo': 'badge-linha-contemporaneo',
                'Clássico': 'badge-linha-classico',
            };
            const classeLinha = classesLinha[p.linha] || 'badge-linha-indefinido';

            let badgeProducao;
            if (p.producao.nao_iniciada) {
                badgeProducao = '<span class="estagio estagio-ausente">Sem itens</span>';
            } else if (p.producao.concluida) {
                badgeProducao = '<span class="estagio estagio-concluido">Concluída</span>';
            } else {
                badgeProducao = `<span class="estagio estagio-em-andamento">Em produção (${p.producao.itens_embalados}/${p.producao.total_itens})</span>`;
            }

            let badgeArmazenagem;
            if (p.producao.nao_iniciada || p.armazenagem.itens_embalados === 0) {
                badgeArmazenagem = '<span class="estagio estagio-ausente">—</span>';
            } else if (p.armazenagem.concluida) {
                badgeArmazenagem = '<span class="estagio estagio-concluido">Concluída</span>';
            } else {
                badgeArmazenagem = `<span class="estagio estagio-em-andamento">Pendente (${p.armazenagem.itens_armazenados}/${p.armazenagem.total_itens})</span>`;
            }

            const badgeFinanceiro = p.financeiro
                ? '<span class="estagio estagio-ativo">Na fila</span>'
                : '<span class="estagio estagio-ausente">—</span>';

            const badgePosVenda = p.pos_venda
                ? '<span class="estagio estagio-ativo">Na fila</span>'
                : '<span class="estagio estagio-ausente">—</span>';

            let badgeExpedicao;
            if (p.finalizado) {
                badgeExpedicao = '<span class="estagio estagio-concluido">Finalizado</span>';
            } else if (p.expedicao_fila) {
                badgeExpedicao = '<span class="estagio estagio-ativo">Na fila</span>';
            } else {
                badgeExpedicao = '<span class="estagio estagio-ausente">—</span>';
            }

            const prazo = p.prazo_producao ? p.prazo_producao.substring(0, 10) : '—';

            return `<tr class="linha-pedido" data-pedido="${p.numero_pedido.toLowerCase()}">
                <td><span class="pedido-numero">${p.numero_pedido}</span></td>
                <td><span class="${classeLinha}">${p.linha}</span></td>
                <td>${prazo}</td>
                <td>${badgeProducao}</td>
                <td>${badgeArmazenagem}</td>
                <td>${badgeFinanceiro}</td>
                <td>${badgePosVenda}</td>
                <td>${badgeExpedicao}</td>
            </tr>`;
        }
    </script>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>
