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

        .badge-pronto.badge-armazenado {
            background-color: #f3e8ff;
            color: #6b21a8;
            border: 1px solid #d8b4fe;
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

        .pedido-numero {
            cursor: default;
        }

        .linha-itens-pedido {
            background-color: #fdfefe;
            display: none;
        }

        .linha-itens-pedido td {
            text-align: left;
            padding: 0 30px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            background: #fafbfe;
        }

        .wrapper-sanfona-itens {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), padding 0.35s ease;
            padding: 0;
        }

        .linha-itens-pedido.aberta .wrapper-sanfona-itens {
            max-height: 260px;
            overflow-y: auto;
            padding: 16px 0;
        }

        .lista-itens-pedido {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .item-status-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid var(--border-tech);
            font-size: 0.85rem;
        }

        .item-status-card .nome-item {
            font-weight: 700;
            color: var(--text-primary);
        }

        .item-status-card .badge-status-item {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .lista-itens-pedido .aviso-carregando,
        .lista-itens-pedido .aviso-vazio {
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-style: italic;
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
                    $origemItens = (stripos($p['numero_pedido'], 'os') !== false) ? 'OS' : 'PRODUCAO';

                    // Essa fila só lista pedido com 100% Embalado (é o
                    // critério de entrada). Aqui só checamos se, além disso,
                    // já virou 100% Armazenado — pra trocar a badge quando o
                    // galpão terminar de conferir tudo.
                    $tabelaItensPedido = ($origemItens === 'OS') ? 'itens_os' : 'itens_producao';
                    $stmtArm = $db->prepare("SELECT COUNT(*) AS total, SUM(status = 'Armazenado') AS armazenados FROM $tabelaItensPedido WHERE numero_pedido = ? AND equipamento NOT LIKE 'Emb.%'");
                    $stmtArm->execute([$p['numero_pedido']]);
                    $infoArm = $stmtArm->fetch(PDO::FETCH_ASSOC);
                    $totalmenteArmazenado = ((int) $infoArm['total'] > 0) && ((int) $infoArm['armazenados'] >= (int) $infoArm['total']);
                ?>
                    <tr id="Linha-<?= $p['id'] ?>" class="linha-pedido">
                        <td style="font-weight: bold; color: #2980b9; font-size: 18px;">
                            <span class="pedido-numero"
                                data-pedido="<?= htmlspecialchars($p['numero_pedido']) ?>"
                                data-origem="<?= $origemItens ?>"
                                onmouseenter="abrirSanfonaItens(<?= $p['id'] ?>, this)"
                                onmouseleave="agendarFechamentoSanfonaItens(<?= $p['id'] ?>)">
                                <?= htmlspecialchars($p['numero_pedido']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?= $badgeLinhaClasse ?>"><?= htmlspecialchars($linhaPedido) ?></span>
                        </td>
                        <td><?= htmlspecialchars(substr($p['prazo_producao'], 0, 10)) ?></td>
                        <td><?= (new DateTime($p['data_conclusao']))->format('d/m/Y H:i') ?></td>
                        <td><span class="badge-pronto<?= $totalmenteArmazenado ? ' badge-armazenado' : '' ?>"><?= $totalmenteArmazenado ? '100% Armazenado' : '100% Embalado' ?></span></td>
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
                    <tr id="ItensRow-<?= $p['id'] ?>" class="linha-itens-pedido">
                        <td colspan="7">
                            <div class="wrapper-sanfona-itens">
                                <div class="lista-itens-pedido" id="lista-itens-<?= $p['id'] ?>">
                                    <span class="aviso-carregando">Passe o mouse no número do pedido para carregar os itens...</span>
                                </div>
                            </div>
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

    <div id="modalMensagem" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); align-items:center; justify-content:center; z-index:1100;">
        <div style="background:#fff; padding:25px; border-radius:12px; max-width:420px; width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.2); text-align:center;">
            <div id="modalMensagemIcone" style="width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px auto; font-size:26px;"></div>
            <h3 id="modalMensagemTitulo" style="margin-top:0; color:#0f172a;"></h3>
            <p id="modalMensagemTexto" style="color:#64748b; font-size:0.9rem; line-height:1.5;"></p>
            <button type="button" id="modalMensagemBotao" onclick="fecharModalMensagem()" style="border:none; color:#fff; padding:10px 24px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; margin-top:10px;">Entendi</button>
        </div>
    </div>

    <div id="modalConfirmacao" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); align-items:center; justify-content:center; z-index:1100;">
        <div style="background:#fff; padding:25px; border-radius:12px; max-width:420px; width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.2); text-align:center;">
            <h3 id="modalConfirmacaoTitulo" style="margin-top:0; color:#0f172a;">Confirmar ação</h3>
            <p id="modalConfirmacaoTexto" style="color:#64748b; font-size:0.9rem; line-height:1.5;"></p>
            <div style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
                <button type="button" onclick="_resolverModalConfirmacao(false)" style="background:#e2e8f0; color:#334155; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.8rem;">Cancelar</button>
                <button type="button" onclick="_resolverModalConfirmacao(true)" style="background:#10b981; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:700; font-size:0.8rem;">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        // Modal de mensagem (substitui alert()) — tipo 'erro' ou 'sucesso'.
        function mostrarModalMensagem(mensagem, opcoes) {
            opcoes = opcoes || {};
            const tipo = opcoes.tipo || 'erro';
            const ehErro = tipo === 'erro';

            const icone = document.getElementById('modalMensagemIcone');
            const titulo = document.getElementById('modalMensagemTitulo');
            const texto = document.getElementById('modalMensagemTexto');
            const botao = document.getElementById('modalMensagemBotao');

            icone.textContent = ehErro ? '⚠️' : '✅';
            icone.style.background = ehErro ? '#fee2e2' : '#d1fae5';
            titulo.textContent = opcoes.titulo || (ehErro ? 'Não foi possível concluir' : 'Sucesso');
            texto.textContent = mensagem || (ehErro ? 'Ocorreu um erro inesperado. Tente novamente.' : '');
            botao.style.background = ehErro ? '#dc2626' : '#10b981';

            document.getElementById('modalMensagem').style.display = 'flex';
        }

        function fecharModalMensagem() {
            document.getElementById('modalMensagem').style.display = 'none';
        }

        // Mantido pelo nome antigo usado nesta tela (dar_baixa_expedicao.php
        // já chamava mostrarModalErro) — repassa pro modal genérico.
        function mostrarModalErro(mensagem, titulo) {
            mostrarModalMensagem(mensagem, {
                titulo: titulo,
                tipo: 'erro'
            });
        }

        function fecharModalErro() {
            fecharModalMensagem();
        }

        // Modal de confirmação (substitui confirm()) — uso: await mostrarModalConfirmacao('Finalizar pedido?')
        let _resolverConfirmacaoAtual = null;

        function mostrarModalConfirmacao(mensagem, titulo) {
            document.getElementById('modalConfirmacaoTitulo').textContent = titulo || 'Confirmar ação';
            document.getElementById('modalConfirmacaoTexto').textContent = mensagem || '';
            document.getElementById('modalConfirmacao').style.display = 'flex';
            return new Promise((resolve) => {
                _resolverConfirmacaoAtual = resolve;
            });
        }

        function _resolverModalConfirmacao(valor) {
            document.getElementById('modalConfirmacao').style.display = 'none';
            if (_resolverConfirmacaoAtual) {
                _resolverConfirmacaoAtual(valor);
                _resolverConfirmacaoAtual = null;
            }
        }
    </script>

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
                mostrarModalMensagem('Informe o motivo da reprogramação.', {
                    titulo: 'Campo obrigatório'
                });
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
                        const linhaItens = document.getElementById(`ItensRow-${id}`);

                        if (linha) {
                            linha.style.transition = "all 0.5s ease";
                            linha.style.opacity = "0";
                            linha.style.background = "#fee2e2";
                        }
                        if (linhaObs) {
                            linhaObs.style.transition = "all 0.5s ease";
                            linhaObs.style.opacity = "0";
                        }
                        if (linhaItens) {
                            linhaItens.style.transition = "all 0.5s ease";
                            linhaItens.style.opacity = "0";
                        }

                        setTimeout(() => {
                            if (linha) linha.remove();
                            if (linhaObs) linhaObs.remove();
                            if (linhaItens) linhaItens.remove();

                            if (document.querySelectorAll('tbody tr:not(.linha-observacao):not(.linha-itens-pedido)').length === 0) {
                                window.location.reload();
                            }
                        }, 600);
                    } else {
                        fecharModalReprogramar();
                        mostrarModalMensagem('Erro ao reprogramar pedido: ' + data.error);
                    }
                })
                .catch(err => {
                    mostrarModalMensagem('Erro na comunicação: ' + err.message);
                });
        }

        document.getElementById('inputPesquisa').addEventListener('keyup', function() {
            const termoBusca = this.value.toLowerCase();
            const linhasPedido = document.querySelectorAll('.linha-pedido');

            linhasPedido.forEach(linha => {
                const idPedido = linha.id.replace('Linha-', '');
                const linhaObs = document.getElementById(`ObsRow-${idPedido}`);
                const linhaItens = document.getElementById(`ItensRow-${idPedido}`);

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
                    if (linhaItens) {
                        linhaItens.classList.remove('aberta');
                        linhaItens.style.display = "none";
                    }
                }
            });
        });

        const temposEsperaFecharItens = {};
        const pedidosItensCarregados = new Set();

        function abrirSanfonaItens(id, spanPedido) {
            clearTimeout(temposEsperaFecharItens[id]);

            const linhaItens = document.getElementById(`ItensRow-${id}`);
            if (!linhaItens) return;

            linhaItens.style.display = "table-row";
            requestAnimationFrame(() => linhaItens.classList.add('aberta'));

            if (pedidosItensCarregados.has(id)) return;
            pedidosItensCarregados.add(id);

            const pedido = spanPedido.getAttribute('data-pedido');
            const origem = spanPedido.getAttribute('data-origem');
            const container = document.getElementById(`lista-itens-${id}`);

            fetch(`../Function/buscar_itens_popup.php?pedido=${encodeURIComponent(pedido)}&origem=${encodeURIComponent(origem)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || data.itens.length === 0) {
                        container.innerHTML = '<span class="aviso-vazio">Nenhum item encontrado para este pedido.</span>';
                        pedidosItensCarregados.delete(id);
                        return;
                    }

                    container.innerHTML = data.itens.map(item => {
                        let corStatus = '#ffeeba; color: #856404;';
                        if (item.status === 'Em produção' || item.status === 'Produzindo') corStatus = '#b8daff; color: #004085;';
                        if (item.status === 'Produzido') corStatus = '#b8daff; color: #004085;';
                        if (item.status === 'Embalado') corStatus = '#c3e6cb; color: #155724;';
                        if (item.status === 'Armazenado') corStatus = '#f0b8ff; color: #3a0242;';

                        return `<div class="item-status-card">
                            <span class="nome-item">${item.nome}</span>
                            <span class="badge-status-item" style="background:${corStatus}">${item.status}</span>
                        </div>`;
                    }).join('');
                })
                .catch(err => {
                    console.error('Erro ao buscar itens do pedido:', err);
                    container.innerHTML = '<span class="aviso-vazio">Erro ao carregar itens.</span>';
                    pedidosItensCarregados.delete(id);
                });
        }

        function agendarFechamentoSanfonaItens(id) {
            temposEsperaFecharItens[id] = setTimeout(() => {
                const linhaItens = document.getElementById(`ItensRow-${id}`);
                if (!linhaItens) return;
                linhaItens.classList.remove('aberta');
                setTimeout(() => {
                    linhaItens.style.display = "none";
                }, 350);
            }, 200);
        }


        document.querySelectorAll('tr.linha-itens-pedido').forEach(tr => {
            const id = tr.id.replace('ItensRow-', '');
            tr.addEventListener('mouseenter', () => clearTimeout(temposEsperaFecharItens[id]));
            tr.addEventListener('mouseleave', () => agendarFechamentoSanfonaItens(id));
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
                mostrarModalMensagem('Erro ao identificar o campo de digitação.');
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
                        mostrarModalMensagem('Observação guardada com sucesso no banco de dados!', {
                            tipo: 'sucesso',
                            titulo: 'Salvo'
                        });
                        toggleSanfona(id);
                    } else {
                        mostrarModalMensagem('Erro ao salvar nota: ' + data.error);
                    }
                })
                .catch(err => console.error("Erro na comunicação:", err));
        }

        async function liberarPedido(id) {
            const confirmado = await mostrarModalConfirmacao('Finalizar Pedido?');
            if (confirmado) {

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
                            const linhaItens = document.getElementById(`ItensRow-${id}`);

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
                            if (linhaItens) {
                                linhaItens.style.transition = "all 0.5s ease";
                                linhaItens.style.opacity = "0";
                            }

                            setTimeout(() => {
                                if (linha) linha.remove();
                                if (linhaObs) linhaObs.remove();
                                if (linhaItens) linhaItens.remove();

                                idsAtuais = idsAtuais.filter(itemId => itemId !== id.toString());

                                if (document.querySelectorAll('tbody tr:not(.linha-observacao):not(.linha-itens-pedido)').length === 0) {
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            mostrarModalMensagem(data.error);
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
                            return;
                        }
                    }
                })
                .catch(err => console.error("Erro na sincronização rápida:", err));
        }
        setInterval(verificarAtualizacoesEmSegundoPlano, 60000);

        async function registrarWebPush() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.warn('Web Push não suportado neste navegador.');
                return;
            }

            try {
                const reg = await navigator.serviceWorker.register('../sw.js');
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    console.log('Permissão para notificações negada.');
                    return;
                }

                let sub = await reg.pushManager.getSubscription();

                if (!sub) {
                    const publicKey = "BCAC-rK4x0HiHSXAjjgt_GsuRVk4Gj3nZaiAnLxeYebmWZtZb82pb0QmOrF764zwtOauecHHP7BvsxdjKjvVgTM";
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(publicKey)
                    });
                }

                await fetch('../Function/salvar_subscricao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(sub)
                });

            } catch (err) {
                console.error('Erro ao registrar Push:', err);
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
        }

        document.addEventListener('DOMContentLoaded', registrarWebPush);
    </script>
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>

</html>