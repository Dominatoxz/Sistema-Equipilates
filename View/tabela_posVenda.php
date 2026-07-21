<?php
date_default_timezone_set('America/Sao_Paulo');
require_once '../Function/trava.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pós-Venda</title>
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-gradient: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            --panel-bg: #ffffff;
            --border-tech: rgba(15, 23, 42, 0.06);

            /* Cores de Identidade Light Tech */
            --tech-blue: #2563eb;
            --tech-blue-hover: #1d4ed8;
            --tech-green: #10b981;
            --tech-green-hover: #059669;
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
            margin-bottom: 30px;
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

        .status-tag {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            background: rgba(15, 23, 42, 0.04);
            padding: 6px 14px;
            border-radius: 20px;
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
            padding: 16px;
            text-align: center;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.04);
            font-size: 0.95rem;
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

        .pedido-numero {
            font-weight: 800;
            color: var(--tech-blue);
            font-size: 1.05rem;
            letter-spacing: 0.5px;
        }

        .btn-baixa {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.15);
        }

        .btn-baixa:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            filter: brightness(1.05);
        }

        .badge-pronto {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #a7f3d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sem-pedidos {
            text-align: center;
            padding: 60px;
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .linha-observacao {
            background-color: #fdfefe;
            display: none;
        }

        .linha-observacao td {
            text-align: left;
            padding: 0 30px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            background: #fafbfe;
        }

        .txt-historico-obs {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 8px 0 0 5px;
            font-style: normal;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .wrapper-sanfona {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), padding 0.35s ease;
            padding: 0;
        }

        .linha-observacao.aberta .wrapper-sanfona {
            max-height: 200px;
            padding: 20px 0;
        }

        .container-obs {
            display: flex;
            gap: 15px;
            align-items: center;
            width: 100%;
        }

        .input-obs {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-primary);
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .input-obs:focus {
            border-color: var(--tech-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-salvar-obs {
            background-color: #0f172a;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s ease;
        }

        .btn-salvar-obs:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        .btn-mais {
            background: rgba(37, 99, 235, 0.05);
            border: 1px solid rgba(37, 99, 235, 0.1);
            color: var(--tech-blue);
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            padding: 6px 14px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-mais:hover {
            background: var(--tech-blue);
            color: #ffffff;
            transform: translateY(-1px);
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
        <h1>Painel Pós-Venda</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Prazo de Produção</th>
                <th>Concluído em</th>
                <th>Status da Fabricação</th>
                <th>Ações</th>
                <th>Mais</th>
            </tr>
        </thead>
        <tbody>
            <?php
            require_once '../config/Database.php';
            require_once '../Model/Sistema.php';

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);

            $pedidos = $sistema->mostrarFilaPosVenda();
            ?>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" class="sem-pedidos">Nenhum pedido aguardando liberação.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p):
                    $stmtObs = $db->prepare("SELECT observacao, DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') as data_obs 
                                             FROM observacoes_posvenda 
                                             WHERE id_pedido = :id 
                                             ORDER BY data_criacao DESC 
                                             LIMIT 1");
                    $stmtObs->bindParam(':id', $p['id']);
                    $stmtObs->execute();
                    $notaExistente = $stmtObs->fetch(PDO::FETCH_ASSOC);

                    $textoNota = $notaExistente ? $notaExistente['observacao'] : '';
                    $dataNota = $notaExistente ? "⏱️ Última alteração: <strong>" . $notaExistente['data_obs'] . "</strong>" : '';
                ?>
                    <tr id="Linha-<?= $p['id'] ?>">
                        <td style="font-weight: bold; color: #2980b9; font-size: 18px;"><?= htmlspecialchars($p['numero_pedido']) ?></td>
                        <td><?= htmlspecialchars(substr($p['prazo_producao'], 0, 10)) ?></td>
                        <td><?= (new DateTime($p['data_conclusao']))->format('d/m/Y H:i') ?></td>
                        <td><span class="badge-pronto">100% Embalado</span></td>
                        <td>
                            <button class="btn-baixa" onclick="liberarPedido(<?= $p['id'] ?>)">Enviar para a Expedição</button>
                        </td>
                        <td>
                            <button class="btn-mais" onclick="toggleSanfona(<?= $p['id'] ?>)">...</button>
                        </td>
                    </tr>
                    <tr id="ObsRow-<?= $p['id'] ?>" class="linha-observacao">
                        <td colspan="6">
                            <div class="wrapper-sanfona">
                                <div class="container-obs">
                                    <input type="text"
                                        id="input-obs-<?= $p['id'] ?>"
                                        class="input-obs"
                                        placeholder="Observações"
                                        value="<?= htmlspecialchars($textoNota) ?>">
                                    <button class="btn-salvar-obs" onclick="salvarObservacaoBanco(<?= $p['id'] ?>, '<?= htmlspecialchars($p['numero_pedido']) ?>')">Salvar Nota</button>
                                </div>
                                <p class="txt-historico-obs" id="time-obs-<?= $p['id'] ?>"><?= $dataNota ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function toggleSanfona(id) {
            const linhaObs = document.getElementById(`ObsRow-${id}`);
            if (!linhaObs) return;

            if (linhaObs.style.display === "table-row") {
                linhaObs.classList.remove('aberta');
                setTimeout(() => {
                    linhaObs.style.display = "none";
                }, 400);
            } else {
                linhaObs.style.display = "table-row";
                setTimeout(() => {
                    linhaObs.classList.add('aberta');
                }, 20);
            }
        }

        function salvarObservacaoBanco(id, numPedido) {
            const elementoInput = document.getElementById(`input-obs-${id}`);
            const timeObs = document.getElementById(`time-obs-${id}`);

            if (!elementoInput) {
                alert("Erro ao identificar o campo de digitação.");
                return;
            }

            const txtObs = elementoInput.value;

            fetch('../Function/salvar_obs_posvenda.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_pedido: id,
                        numero_pedido: numPedido,
                        observacao: txtObs
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const agora = new Date().toLocaleString('pt-BR');
                        if (timeObs) {
                            timeObs.innerHTML = `⏱️ Última alteração salva em: <strong>${agora}</strong>`;
                        }
                        alert("Observação guardada com sucesso no banco de dados!");
                        toggleSanfona(id);
                    } else {
                        alert("Erro ao salvar nota: " + data.error);
                    }
                })
                .catch(err => console.error("Erro na comunicação:", err));
        }

        function liberarPedido(id) {
            if (confirm("Enviar pedido para a Expedição?")) {

                const dadosEnviar = {
                    id_pedido: id
                };
                fetch('../Function/dar_baixa_posVenda.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(dadosEnviar)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const linha = document.getElementById(`Linha-${id}`);
                            const linhaObs = document.getElementById(`ObsRow-${id}`);

                            localStorage.removeItem(`obs_pedido_${id}`);

                            if (linha) {
                                linha.style.transition = "all 0.5s ease";
                                linha.style.opacity = "0";
                                linha.style.background = "#e8f5e9";
                            }
                            if (linhaObs) {
                                linhaObs.style.transition = "all 0.5s ease";
                                linhaObs.style.opacity = "0";
                            }

                            setTimeout(() => {
                                if (linha) linha.remove();
                                if (linhaObs) linhaObs.remove();

                                if (document.querySelectorAll('tbody tr:not(.linha-observacao)').length === 0) {
                                    window.location.reload();
                                }
                            }, 10000);
                        } else {
                            alert("Erro ao dar baixa no sistema: " + data.error);
                        }
                    })
                    .catch(err => console.error("Erro na comunicação:", err));
            }
        }



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

        setInterval(verificarAtualizacoesEmSegundoPlano, 60000);
    </script>
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>