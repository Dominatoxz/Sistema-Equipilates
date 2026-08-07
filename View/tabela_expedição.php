<?php
date_default_timezone_set('America/Sao_Paulo');
require_once '../Function/trava.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expedição</title>
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
            padding: 16px;
            text-align: center;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 10;
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

        .btn-reprogramar {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.15);
            margin-left: 8px;
        }

        .btn-reprogramar:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
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

        .badge-linha-misto {
            background-color: #f3e8ff;
            color: #6b21a8;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #d8b4fe;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-linha-contemporaneo {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #93c5fd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-linha-classico {
            background-color: #fef3c7;
            color: #92400e;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #fde68a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-linha-indefinido {
            background-color: #f1f5f9;
            color: #475569;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
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
        <h1>Painel Expedição</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>

    <div class="container-pesquisa">
        <input type="text" id="inputPesquisa" class="input-pesquisa" placeholder="🔍 Buscar por Pedido / OS...">
    </div>

    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Linha</th>
                <th>Prazo de Produção</th>
                <th>Concluído em</th>
                <th>Status da Fabricação</th>
                <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], CARGOS_EXPEDICAO_ACAO)): ?>
                    <th>Ações</th>
                <?php endif; ?>
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

            $pedidos = $sistema->mostrarFilaExpedicao();
            ?>
            <?php if (empty($pedidos)): ?>
                <tr class="linha-sem-dados">
                    <td colspan="7" class="sem-pedidos">Nenhum pedido aguardando liberação.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p):
                    $stmtObs = $db->prepare("SELECT observacao, DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') as data_obs 
                                             FROM observacoes_expedicao 
                                             WHERE id_pedido = :id 
                                             ORDER BY data_criacao DESC 
                                             LIMIT 1");
                    $stmtObs->bindParam(':id', $p['id']);
                    $stmtObs->execute();
                    $notaExistente = $stmtObs->fetch(PDO::FETCH_ASSOC);

                    $textoNota = $notaExistente ? $notaExistente['observacao'] : '';
                    $dataNota = $notaExistente ? "⏱️ Última alteração: <strong>" . $notaExistente['data_obs'] . "</strong>" : '';

                    $linhaPedido = $sistema->linhaDoPedido($p['numero_pedido']);
                    $badgeLinhaClasse = match ($linhaPedido) {
                        'Misto' => 'badge-linha-misto',
                        'Contemporâneo' => 'badge-linha-contemporaneo',
                        'Clássico' => 'badge-linha-classico',
                        default => 'badge-linha-indefinido',
                    };
                ?>
                    <tr id="Linha-<?= $p['id'] ?>" class="linha-pedido">
                        <td style="font-weight: bold; color: #2980b9; font-size: 18px;"><?= htmlspecialchars($p['numero_pedido']) ?></td>
                        <td>
                            <span class="<?= $badgeLinhaClasse ?>"><?= htmlspecialchars($linhaPedido) ?></span>
                        </td>
                        <td><?= htmlspecialchars(substr($p['prazo_producao'], 0, 10)) ?></td>
                        <td><?= (new DateTime($p['data_conclusao']))->format('d/m/Y H:i') ?></td>
                        <td><span class="badge-pronto">100% Embalado</span></td>
                        <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], CARGOS_EXPEDICAO_ACAO)): ?>
                            <td>
                                <button class="btn-baixa" onclick="liberarPedido(<?= $p['id'] ?>)">Finalizar Pedido</button>
                                <button class="btn-reprogramar" onclick="abrirModalReprogramar(<?= $p['id'] ?>)">Remover Pedido</button>
                            </td>
                        <?php endif; ?>
                        <td>
                            <button class="btn-mais" onclick="toggleSanfona(<?= $p['id'] ?>)">...</button>
                        </td>
                    </tr>
                    <tr id="ObsRow-<?= $p['id'] ?>" class="linha-observacao">
                        <td colspan="7">
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

    <div id="modalReprogramar" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); align-items:center; justify-content:center; z-index:1000;">
        <div style="background:#fff; padding:25px; border-radius:12px; max-width:420px; width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
            <h3 style="margin-top:0; color:#0f172a;">Reprogramar Pedido</h3>
            <p style="color:#64748b; font-size:0.9rem;">Explique o motivo pelo qual este pedido está saindo desta fila. Ele será registrado e removido daqui.</p>
            <textarea id="inputMotivoReprogramar" rows="4" style="width:100%; box-sizing:border-box; padding:10px; border:1px solid #e2e8f0; border-radius:8px; font-size:0.9rem; font-family:inherit;" placeholder="Descreva o motivo..."></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button type="button" onclick="fecharModalReprogramar()" style="background:#e2e8f0; color:#334155; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.8rem;">Cancelar</button>
                <button type="button" onclick="confirmarReprogramacao()" style="background:#dc2626; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.8rem;">Confirmar e Remover</button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = <?= json_encode(gerarTokenCSRF()) ?>;
        let idReprogramarAtual = null;

        function abrirModalReprogramar(id) {
            idReprogramarAtual = id;
            document.getElementById('inputMotivoReprogramar').value = '';
            document.getElementById('modalReprogramar').style.display = 'flex';
        }

        function fecharModalReprogramar() {
            document.getElementById('modalReprogramar').style.display = 'none';
            idReprogramarAtual = null;
        }

        function confirmarReprogramacao() {
            const motivo = document.getElementById('inputMotivoReprogramar').value.trim();
            if (!motivo) {
                alert('Informe o motivo da reprogramação.');
                return;
            }
            if (!idReprogramarAtual) return;

            const id = idReprogramarAtual;

            fetch('../Function/reprogramar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_pedido: id,
                        motivo: motivo,
                        origem_tela: 'expedicao',
                        csrf_token: csrfToken
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fecharModalReprogramar();

                        const linha = document.getElementById(`Linha-${id}`);
                        const linhaObs = document.getElementById(`ObsRow-${id}`);

                        if (linha) {
                            linha.style.transition = "all 0.5s ease";
                            linha.style.opacity = "0";
                            linha.style.background = "#fee2e2";
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
                        }, 600);
                    } else {
                        alert('Erro ao reprogramar pedido: ' + data.error);
                        fecharModalReprogramar();
                    }
                })
                .catch(err => {
                    alert('Erro na comunicação: ' + err.message);
                });
        }

        document.getElementById('inputPesquisa').addEventListener('keyup', function() {
            const termoBusca = this.value.toLowerCase();
            const linhasPedido = document.querySelectorAll('.linha-pedido');

            linhasPedido.forEach(linha => {
                const idPedido = linha.id.replace('Linha-', '');
                const linhaObs = document.getElementById(`ObsRow-${idPedido}`);

                const textoPedido = linha.getElementsByTagName('td')[0].textContent.toLowerCase();

                if (textoPedido.includes(termoBusca)) {
                    linha.style.display = "";
                    if (linhaObs && !linhaObs.classList.contains('aberta')) {
                        linhaObs.style.display = "none";
                    }
                } else {
                    linha.style.display = "none";
                    if (linhaObs) {
                        linhaObs.style.display = "none";
                    }
                }
            });
        });

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

            fetch('../Function/salvar_obs_expedicao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_pedido: id,
                        numero_pedido: numPedido,
                        observacao: txtObs,
                        csrf_token: csrfToken
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
            if (confirm("Finalizar Pedido?")) {

                const dadosEnviar = {
                    id_pedido: id,
                    csrf_token: csrfToken
                };
                fetch('../Function/dar_baixa_expedicao.php', {
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
                                opacity = "0";
                                linha.style.background = "#e8f5e9";
                            }
                            if (linhaObs) {
                                linhaObs.style.transition = "all 0.5s ease";
                                opacity = "0";
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

        let idsAtuais = Array.from(document.querySelectorAll('tbody tr[id^="Linha-"]'))
            .map(tr => tr.id.replace('Linha-', ''));

        function verificarAtualizacoesEmSegundoPlano() {
            if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                return;
            }

            fetch('../Function/dados_tabelas.php?tela=expedicao')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosDados = data.dados;
                        const novosIds = novosDados.map(p => p.id.toString());

                        const temNovoItem = novosIds.some(id => !idsAtuais.includes(id));
                        const itemSumiu = idsAtuais.some(id => !novosIds.includes(id));

                        if (temNovoItem || itemSumiu) {
                            window.location.reload();
                            return
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