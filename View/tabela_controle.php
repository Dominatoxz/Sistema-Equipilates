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

        .sem-pedidos {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-size: 18px;
            font-weight: 500;
        }

        .badge-origem {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .origem-normal {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .origem-os {
            background-color: #f8d7da;
            color: #721c24;
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

        .footer {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
        }
    </style>
</head>

<body>
    <div class="header-painel">
        <h1>Painel de Controle</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>

    <div class="filtros-container">
        <button class="btn-filtro active" data-filter="todos">
            Todos <span id="qtd-todos" style="background: #e2e8f0; color: #334155;">0</span>
        </button>
        <button class="btn-filtro" data-filter="normal">
            Pedidos Normais <span id="qtd-normal" style="background: #e2e3e5; color: #383d41;">0</span>
        </button>
        <button class="btn-filtro" data-filter="os">
            Ordens de Serviço (OS) <span id="qtd-os" style="background: #f8d7da; color: #721c24;">0</span>
        </button>
        <input type="text" id="inputPesquisa" class="input-pesquisa" placeholder="🔍 Buscar por Pedido / OS...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Tipo</th>
                <th>Prazo de Produção</th>
                <th>Status Pós-Produção</th>
            </tr>
        </thead>
        <tbody id="tabela-controle-body">
            <?php
            require_once '../config/Database.php';
            require_once '../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);

            $pedidos = $sistema->mostrarFilaControle();
            ?>
            <?php if (empty($pedidos)): ?>
                <tr class="linha-sem-registro">
                    <td colspan="4" class="sem-pedidos">Nenhum pedido aguardando liberação.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p):
                    $numPedido = $p['numero_pedido'] ?? '';
                    $tipoOrigem = (strpos(strtoupper($numPedido), 'OS') !== false) ? 'OS' : 'Normal';
                    $classeOrigem = ($tipoOrigem === 'OS') ? 'origem-os' : 'origem-normal';
                ?>
                    <tr id="linha-<?= $p['id'] ?>" data-tipo="<?= strtolower($tipoOrigem) ?>">
                        <td style="font-weight: bold; color: #2980b9; font-size: 18px;">
                            <?= htmlspecialchars($numPedido) ?>
                        </td>
                        <td>
                            <span class="badge-origem <?= $classeOrigem ?>">
                                <?= $tipoOrigem ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($p['prazo_producao'], 0, 10)) ?></td>
                        <td><?= htmlspecialchars($p['status_posvenda']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        const botoesFiltro = document.querySelectorAll('.btn-filtro');
        const inputPesquisa = document.getElementById('inputPesquisa');

        function atualizarContadores() {
            const linhasTabela = document.querySelectorAll('#tabela-controle-body tr[id^="linha-"]');
            let todos = 0, normal = 0, os = 0;

            linhasTabela.forEach(tr => {
                todos++;
                const tipo = tr.getAttribute('data-tipo');
                if (tipo === 'normal') normal++;
                if (tipo === 'os') os++;
            });

            document.getElementById('qtd-todos').textContent = todos;
            document.getElementById('qtd-normal').textContent = normal;
            document.getElementById('qtd-os').textContent = os;
        }

        function aplicarFiltrosCombinados() {
            const botaoAtivo = document.querySelector('.btn-filtro.active');
            const filtroSelecionado = botaoAtivo.getAttribute('data-filter');
            const termoBusca = inputPesquisa.value.toLowerCase();
            
            const linhasTabela = document.querySelectorAll('#tabela-controle-body tr[id^="linha-"]');
            let encontrouAlgum = false;

            linhasTabela.forEach(tr => {
                const tipoLinha = tr.getAttribute('data-tipo');
                const textoPedido = tr.getElementsByTagName('td')[0].textContent.toLowerCase();

                const atendeFiltro = (filtroSelecionado === 'todos' || tipoLinha === filtroSelecionado);
                const atendeBusca = textoPedido.includes(termoBusca);

                if (atendeFiltro && atendeBusca) {
                    tr.style.display = '';
                    encontrouAlgum = true;
                } else {
                    tr.style.display = 'none';
                }
            });

            const avisoExistente = document.querySelector('.aviso-filtro-vazio');
            if (avisoExistente) avisoExistente.remove();

            if (!encontrouAlgum && linhasTabela.length > 0) {
                const tbody = document.getElementById('tabela-controle-body');
                const trAviso = document.createElement('tr');
                trAviso.className = 'aviso-filtro-vazio';
                trAviso.innerHTML = `<td colspan="4" class="sem-pedidos">Nenhum registro encontrado para a busca atual.</td>`;
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

        let idsAtuais = Array.from(document.querySelectorAll('tbody tr[id^="linha-"]'))
            .map(tr => tr.id.replace('linha-', ''));

        function verificarAtualizacoesEmSegundoPlano() {
            if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                return;
            }

            fetch('../Function/dados_tabelas.php?tela=pos_venda')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosDados = data.dados;
                        const novosIds = novosDados.map(p => p.id.toString());

                        const temNovoItem = novosIds.some(id => !idsAtuais.includes(id));
                        const itemSumiu = idsAtuais.some(id => !novosIds.includes(id));

                        if (temNovoItem || itemSumiu) {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => console.error("Erro na sincronização rápida:", err));
        }

        setInterval(verificarAtualizacoesEmSegundoPlano, 30000);
    </script>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>