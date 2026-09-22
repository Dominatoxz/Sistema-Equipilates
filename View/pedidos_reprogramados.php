<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
date_default_timezone_set('America/Sao_Paulo');

verificarAcessoSetor(CARGOS_PEDIDOS_REPROGRAMADOS);

$database = new Database();
$db = $database->getConnection();
$sistema = new Sistema($db);

$filtro_pedido      = isset($_GET['filtro_pedido']) ? trim($_GET['filtro_pedido']) : '';
$filtro_linha       = isset($_GET['filtro_linha']) ? trim($_GET['filtro_linha']) : '';
$filtro_origem_tela = isset($_GET['filtro_origem_tela']) ? trim($_GET['filtro_origem_tela']) : '';
$filtro_data_ini    = isset($_GET['filtro_data_ini']) ? trim($_GET['filtro_data_ini']) : '';
$filtro_data_fim    = isset($_GET['filtro_data_fim']) ? trim($_GET['filtro_data_fim']) : '';
$apenas_confirmados = isset($_GET['apenas_confirmados']) && $_GET['apenas_confirmados'] === '1';

$registros = $sistema->mostrarPedidosReprogramados([
    'pedido'             => $filtro_pedido,
    'linha'              => $filtro_linha,
    'origem_tela'        => $filtro_origem_tela,
    'data_ini'           => $filtro_data_ini,
    'data_fim'           => $filtro_data_fim,
    'apenas_confirmados' => $apenas_confirmados,
]);

$rotulosOrigem = [
    'financeiro' => 'Financeiro',
    'posvenda'   => 'Pós-Venda',
    'expedicao'  => 'Expedição',
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Reprogramados</title>
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-gradient: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            --panel-bg: #ffffff;
            --border-tech: rgba(15, 23, 42, 0.06);

            --tech-blue: #2563eb;
            --tech-red: #dc2626;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
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
            margin: 0;
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
        }

        .btn-voltar {
            background: #7f8c8d;
            color: white;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-voltar:hover {
            background: #626567;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
            background: var(--panel-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-tech);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            min-width: 170px;
            box-sizing: border-box;
            height: 40px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--tech-blue);
            outline: none;
        }

        .btn-action {
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            height: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .btn-filtrar {
            background: var(--tech-blue);
            color: white;
        }

        .btn-filtrar:hover {
            background: #1d4ed8;
        }

        .btn-filtro-ok {
            background: #16a34a;
            color: white;
        }

        .btn-filtro-ok:hover {
            background: #15803d;
        }

        .btn-filtro-ok.ativo {
            background: #0f172a;
        }

        .btn-limpar {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-limpar:hover {
            background: #cbd5e1;
        }

        .btn-finalizar {
            background: #16a34a;
            color: white;
            padding: 8px 14px;
            font-size: 12px;
            height: auto;
        }

        .btn-finalizar:hover {
            background: #15803d;
        }

        .btn-finalizar:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .badge-ok {
            background: #dcfce7;
            color: #15803d;
            font-weight: 700;
        }

        .info-confirmado {
            font-size: 0.75rem;
            color: var(--text-secondary);
            display: block;
            margin-top: 4px;
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
            padding: 14px;
            text-align: center;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.04);
            font-size: 0.9rem;
            color: var(--text-primary);
            text-align: center;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-contemporaneo {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-classico {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-misto {
            background: #ede9fe;
            color: #6d28d9;
        }

        .badge-indefinido {
            background: #e2e8f0;
            color: #475569;
        }

        .badge-origem {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .motivo-texto {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-style: italic;
            text-align: left;
            max-width: 320px;
        }

        .sem-registros {
            text-align: center;
            padding: 60px;
            color: var(--text-secondary);
            font-size: 1.1rem;
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
        <h1>Pedidos Reprogramados <?= $apenas_confirmados ? '— Já Ajustados' : '— Pendentes de Ajuste' ?></h1>
        <a href="../index.php" class="btn-voltar">← Voltar para a Central</a>
    </div>

    <form method="GET" class="filter-form">
        <input type="hidden" name="apenas_confirmados" value="<?= $apenas_confirmados ? '1' : '0' ?>">

        <div class="filter-group">
            <label for="filtro_pedido">Pedido / OS</label>
            <input type="text" name="filtro_pedido" id="filtro_pedido" value="<?= htmlspecialchars($filtro_pedido) ?>" placeholder="Ex: 4806">
        </div>

        <div class="filter-group">
            <label for="filtro_linha">Linha</label>
            <select name="filtro_linha" id="filtro_linha">
                <option value="" <?= $filtro_linha === '' ? 'selected' : '' ?>>Todas</option>
                <option value="Contemporâneo" <?= $filtro_linha === 'Contemporâneo' ? 'selected' : '' ?>>Contemporâneo</option>
                <option value="Clássico" <?= $filtro_linha === 'Clássico' ? 'selected' : '' ?>>Clássico</option>
                <option value="Misto" <?= $filtro_linha === 'Misto' ? 'selected' : '' ?>>Misto</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="filtro_origem_tela">Saiu de</label>
            <select name="filtro_origem_tela" id="filtro_origem_tela">
                <option value="" <?= $filtro_origem_tela === '' ? 'selected' : '' ?>>Todas</option>
                <option value="financeiro" <?= $filtro_origem_tela === 'financeiro' ? 'selected' : '' ?>>Financeiro</option>
                <option value="posvenda" <?= $filtro_origem_tela === 'posvenda' ? 'selected' : '' ?>>Pós-Venda</option>
                <option value="expedicao" <?= $filtro_origem_tela === 'expedicao' ? 'selected' : '' ?>>Expedição</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="filtro_data_ini">De</label>
            <input type="date" name="filtro_data_ini" id="filtro_data_ini" value="<?= htmlspecialchars($filtro_data_ini) ?>">
        </div>

        <div class="filter-group">
            <label for="filtro_data_fim">Até</label>
            <input type="date" name="filtro_data_fim" id="filtro_data_fim" value="<?= htmlspecialchars($filtro_data_fim) ?>">
        </div>

        <button type="submit" class="btn-action btn-filtrar">Aplicar Filtros</button>
        <a href="pedidos_reprogramados.php" class="btn-action btn-limpar">Limpar</a>

        <button type="button" class="btn-action btn-filtro-ok <?= $apenas_confirmados ? 'ativo' : '' ?>" onclick="alternarModo()">
            <?= $apenas_confirmados ? '← Ver Pendentes' : 'Pedidos já Reprogramados ✓' ?>
        </button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Linha</th>
                <th>Saiu de</th>
                <th>Motivo</th>
                <th>Usuário</th>
                <th>Data/Hora</th>
                <th><?= $apenas_confirmados ? 'Confirmado' : 'Finalizar' ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr>
                    <td colspan="7" class="sem-registros">
                        <?= $apenas_confirmados ? 'Nenhum pedido confirmado como já ajustado ainda.' : 'Nenhum pedido reprogramado pendente encontrado.' ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($registros as $r): ?>
                    <tr id="linha-<?= $r['id'] ?>">
                        <td style="font-weight: bold; color: #2980b9; font-size: 16px;"><?= htmlspecialchars($r['numero_pedido']) ?></td>
                        <td>
                            <?php
                            $classeLinha = [
                                'Contemporâneo' => 'badge-contemporaneo',
                                'Clássico'      => 'badge-classico',
                                'Misto'         => 'badge-misto',
                                'Indefinido'    => 'badge-indefinido',
                            ][$r['linha']] ?? 'badge-indefinido';
                            ?>
                            <span class="badge <?= $classeLinha ?>"><?= htmlspecialchars($r['linha']) ?></span>
                        </td>
                        <td><span class="badge-origem"><?= htmlspecialchars($rotulosOrigem[$r['origem_tela']] ?? $r['origem_tela']) ?></span></td>
                        <td class="motivo-texto"><?= htmlspecialchars($r['motivo']) ?></td>
                        <td><?= htmlspecialchars($r['usuario_nome'] ?? '—') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></td>
                        <td>
                            <?php if ($apenas_confirmados): ?>
                                <span class="badge badge-ok">✓ OK</span>
                                <span class="info-confirmado">
                                    <?= htmlspecialchars($r['confirmado_por'] ?? '—') ?><br>
                                    <?= $r['confirmado_em'] ? date('d/m/Y H:i', strtotime($r['confirmado_em'])) : '' ?>
                                </span>
                            <?php else: ?>
                                <button class="btn-action btn-finalizar" onclick="finalizarPedido(<?= $r['id'] ?>, this)">Finalizar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>

    <script>
        const csrfToken = <?= json_encode(gerarTokenCSRF()) ?>;

        function alternarModo() {
            const params = new URLSearchParams(window.location.search);
            params.set('apenas_confirmados', params.get('apenas_confirmados') === '1' ? '0' : '1');
            window.location.href = 'pedidos_reprogramados.php?' + params.toString();
        }

        async function finalizarPedido(id, botao) {
            if (!confirm('Confirmar que este pedido já foi ajustado fisicamente?')) {
                return;
            }

            botao.disabled = true;
            botao.textContent = 'Salvando...';

            try {
                const resposta = await fetch('../Function/confirmar_pedido_reprogramado.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, csrf_token: csrfToken })
                });
                const data = await resposta.json();

                if (data.success) {
                    const linha = document.getElementById('linha-' + id);
                    if (linha) linha.remove();
                } else {
                    alert('Erro: ' + data.error);
                    botao.disabled = false;
                    botao.textContent = 'Finalizar';
                }
            } catch (e) {
                alert('Erro de conexão. Tente novamente.');
                botao.disabled = false;
                botao.textContent = 'Finalizar';
            }
        }
    </script>
</body>

</html>