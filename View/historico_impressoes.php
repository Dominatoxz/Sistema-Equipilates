<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
date_default_timezone_set('America/Sao_Paulo');

verificarAcessoSetor(CARGOS_HISTORICO_IMPRESSOES);

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT ie.id, ie.id_item, ie.tabela_origem, ie.tipo_etiqueta, ie.usuario_nome,
               ie.motivo_reimpressao, ie.criado_em,
               COALESCE(p.numero_pedido, o.numero_pedido) AS numero_pedido,
               COALESCE(p.equipamento, o.equipamento) AS equipamento
        FROM impressoes_etiquetas ie
        LEFT JOIN itens_producao p ON ie.tabela_origem = 'PRODUCAO' AND ie.id_item = p.id
        LEFT JOIN itens_os o ON ie.tabela_origem = 'OS' AND ie.id_item = o.id
        ORDER BY ie.criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Impressões</title>
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-gradient: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            --panel-bg: #ffffff;
            --border-tech: rgba(15, 23, 42, 0.06);

            --tech-blue: #2563eb;
            --tech-green: #10b981;
            --tech-orange: #f59e0b;
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
            color: var(--text-primary);
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

        .container-pesquisa {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .input-pesquisa {
            width: 100%;
            max-width: 400px;
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
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-producao {
            background: #e2e8f0;
            color: #334155;
        }

        .badge-embalagem {
            background: #0f172a;
            color: #fff;
        }

        .badge-reimpressao {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-primeira {
            background: #d1fae5;
            color: #065f46;
        }

        .motivo-texto {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-style: italic;
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
        <h1>Histórico de Impressões de Etiquetas</h1>
        <a href="Contemporaneo/central_contemporaneo.php" class="btn-voltar">← Voltar para a Central</a>
    </div>

    <div class="container-pesquisa">
        <input type="text" id="inputPesquisa" class="input-pesquisa" placeholder="🔍 Buscar por Pedido / OS...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Item</th>
                <th>Origem</th>
                <th>Tipo</th>
                <th>Situação</th>
                <th>Usuário</th>
                <th>Motivo (reimpressão)</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr>
                    <td colspan="8" class="sem-registros">Nenhuma impressão registrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($registros as $r): ?>
                    <tr class="linha-registro">
                        <td style="font-weight: bold; color: #2980b9; font-size: 16px;"><?= htmlspecialchars($r['numero_pedido'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['equipamento'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['tabela_origem']) ?></td>
                        <td>
                            <span class="badge <?= $r['tipo_etiqueta'] === 'EMBALAGEM' ? 'badge-embalagem' : 'badge-producao' ?>">
                                <?= htmlspecialchars($r['tipo_etiqueta']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($r['motivo_reimpressao'])): ?>
                                <span class="badge badge-reimpressao">Reimpressão</span>
                            <?php else: ?>
                                <span class="badge badge-primeira">1ª Impressão</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['usuario_nome'] ?? '—') ?></td>
                        <td class="motivo-texto"><?= !empty($r['motivo_reimpressao']) ? htmlspecialchars($r['motivo_reimpressao']) : '—' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['criado_em'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        document.getElementById('inputPesquisa').addEventListener('keyup', function() {
            const termoBusca = this.value.toLowerCase();
            const linhas = document.querySelectorAll('.linha-registro');

            linhas.forEach(function(linha) {
                const textoPedido = linha.getElementsByTagName('td')[0].textContent.toLowerCase();
                linha.style.display = textoPedido.includes(termoBusca) ? "" : "none";
            });
        });
    </script>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>