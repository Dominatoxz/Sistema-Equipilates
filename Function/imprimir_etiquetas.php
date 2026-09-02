<?php
require_once '../Function/trava.php';
require_once '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

require_once '../config/Database.php';
require_once '../Model/Sistema.php';

$db = (new Database())->getConnection();
$generator = new BarcodeGeneratorPNG();

$sistema = new Sistema($db);
$pedidosMistos = array_unique(array_merge(
    $sistema->pedidosMistos('itens_producao'),
    $sistema->pedidosMistos('itens_os')
));

$filtro_pedido    = isset($_GET['filtro_pedido']) ? trim($_GET['filtro_pedido']) : '';
$filtro_item      = isset($_GET['filtro_item']) ? trim($_GET['filtro_item']) : '';
$filtro_tipo_eti  = isset($_GET['filtro_tipo_eti']) ? trim($_GET['filtro_tipo_eti']) : 'todos';
$filtro_data_ini  = isset($_GET['filtro_data_ini']) ? trim($_GET['filtro_data_ini']) : '';
$filtro_data_fim  = isset($_GET['filtro_data_fim']) ? trim($_GET['filtro_data_fim']) : '';

// Nomes de equipamento se sobrepõem por prefixo (ex: "CARRINHO CLASSICO" é
// prefixo de "CARRINHO CLASSICO TORRE"/"TAUARI"/"HIBRIDO"), então um LIKE
// '%...%' sempre pega os dois. Só usamos LIKE quando o texto digitado não
// bate exatamente com nenhum nome de equipamento já cadastrado — quando
// bate, é sinal de que o usuário quer só aquele item específico.
$stmtItensDisponiveis = $db->query(
    "SELECT DISTINCT equipamento FROM itens_producao WHERE equipamento NOT LIKE 'Emb.%'
     UNION
     SELECT DISTINCT equipamento FROM itens_os WHERE equipamento NOT LIKE 'Emb.%'
     ORDER BY equipamento ASC"
);
$itensDisponiveis = $stmtItensDisponiveis->fetchAll(PDO::FETCH_COLUMN);

$filtroItemExato = false;
foreach ($itensDisponiveis as $nomeDisponivel) {
    if (mb_strtolower(trim($nomeDisponivel)) === mb_strtolower($filtro_item)) {
        $filtroItemExato = true;
        break;
    }
}

$params = [];

$sql_producao = "SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, qualidade_tentativas, 'PRODUCAO' AS tabela_origem
                 FROM itens_producao
                 WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'";

if (!empty($filtro_pedido)) {
    $sql_producao .= " AND numero_pedido LIKE :pedido1";
    $params[':pedido1'] = '%' . $filtro_pedido . '%';
}
if (!empty($filtro_item)) {
    $sql_producao .= $filtroItemExato ? " AND equipamento = :item1" : " AND equipamento LIKE :item1";
    $params[':item1'] = $filtroItemExato ? $filtro_item : '%' . $filtro_item . '%';
}

if (!empty($filtro_data_ini) && !empty($filtro_data_fim)) {
    $sql_producao .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') BETWEEN :data_ini1 AND :data_fim1";
    $params[':data_ini1'] = $filtro_data_ini;
    $params[':data_fim1'] = $filtro_data_fim;
} elseif (!empty($filtro_data_ini)) {
    $sql_producao .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') >= :data_ini1";
    $params[':data_ini1'] = $filtro_data_ini;
} elseif (!empty($filtro_data_fim)) {
    $sql_producao .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') <= :data_fim1";
    $params[':data_fim1'] = $filtro_data_fim;
}

$sql_os = "SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, qualidade_tentativas, 'OS' AS tabela_origem
           FROM itens_os
           WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'";

if (!empty($filtro_pedido)) {
    $sql_os .= " AND numero_pedido LIKE :pedido2";
    $params[':pedido2'] = '%' . $filtro_pedido . '%';
}
if (!empty($filtro_item)) {
    $sql_os .= $filtroItemExato ? " AND equipamento = :item2" : " AND equipamento LIKE :item2";
    $params[':item2'] = $filtroItemExato ? $filtro_item : '%' . $filtro_item . '%';
}

if (!empty($filtro_data_ini) && !empty($filtro_data_fim)) {
    $sql_os .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') BETWEEN :data_ini2 AND :data_fim2";
    $params[':data_ini2'] = $filtro_data_ini;
    $params[':data_fim2'] = $filtro_data_fim;
} elseif (!empty($filtro_data_ini)) {
    $sql_os .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') >= :data_ini2";
    $params[':data_ini2'] = $filtro_data_ini;
} elseif (!empty($filtro_data_fim)) {
    $sql_os .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') <= :data_fim2";
    $params[':data_fim2'] = $filtro_data_fim;
}

$query = "($sql_producao) UNION ALL ($sql_os) ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC, id ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Impressão de Etiquetas</title>
    <style>
        @page {
            size: 101mm 50mm;
            margin: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            background: #fff;
            padding: 20px;
            border-bottom: 3px solid #2c3e50;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .header-filtros {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-filtros h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 22px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: bold;
            color: #555;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            min-width: 180px;
            box-sizing: border-box;
            height: 40px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #2980b9;
            outline: none;
        }

        .btn-action {
            padding: 10px 22px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s, color 0.2s;
            height: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .btn-filtrar {
            background: #2980b9;
            color: white;
        }

        .btn-filtrar:hover {
            background: #1f618d;
        }

        .btn-limpar {
            background: #e2e3e5;
            color: #383d41;
        }

        .btn-limpar:hover {
            background: #d6d8db;
        }

        .btn-print {
            background: #27ae60;
            color: white;
            margin-left: auto;
        }

        .btn-print:hover {
            background: #219150;
        }

        .btn-voltar {
            background: #7f8c8d;
            color: white;
            font-size: 13px;
            padding: 8px 16px;
            height: 35px;
        }

        .btn-voltar:hover {
            background: #626567;
        }

        .container-gabarito {
            display: block;
            padding: 10px;
        }

        .etiqueta {
            background: white;
            border: 1px dashed #333;
            padding: 4mm 5mm;
            box-sizing: border-box;
            height: 50mm;
            width: 101mm;
            margin: 10px auto;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .etiqueta-fabrica {
            border-left: 6mm solid #666666;
        }

        .etiqueta-embalagem {
            border-left: 6mm solid #000000;
        }

        .etiqueta-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            row-gap: 2px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        .etiqueta-header-pedido {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .num-pedido {
            font-size: 22px;
            font-weight: bold;
            color: #000;
        }

        .tipo-etiqueta {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
        }

        .badge-misto-etiqueta {
            display: inline-block;
            background: #000;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 3px;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }

        .etiqueta-titulo {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .etiqueta-sub,
        .etiqueta-cor {
            font-size: 11px;
            color: #000;
            line-height: 14px;
        }

        .barcodeSection {
            position: absolute;
            bottom: 5mm;
            left: 15mm;
            right: 5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .barcode {
            width: 85%;
            height: 10mm;
            object-fit: fill;
        }

        .id-text {
            font-family: monospace;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
            margin-top: 2px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .container-gabarito {
                padding: 0;
                margin: 0;
            }

            .etiqueta {
                margin: 0;
                border: none;
            }

            .etiqueta-fabrica {
                border-left: 6mm solid #666666 !important;
            }

            .etiqueta-embalagem {
                border-left: 6mm solid #000000 !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <div class="header-filtros">
            <h2>Filtros para Impressão de Etiquetas</h2>
            <div style="display: flex; gap: 10px;">
                <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], CARGOS_HISTORICO_IMPRESSOES)): ?>
                    <a href="../View/historico_impressoes.php" class="btn-action btn-voltar">📜 Histórico de Impressões</a>
                <?php endif; ?>
                <a href="../index.php" class="btn-action btn-voltar">← Voltar para a Central</a>
            </div>
        </div>

        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="filtro_pedido">Nº do Pedido / OS:</label>
                <input type="text" name="filtro_pedido" id="filtro_pedido" value="<?= htmlspecialchars($filtro_pedido) ?>" placeholder="Ex: 4806">
            </div>

            <div class="filter-group">
                <label for="filtro_item">Nome do Item:</label>
                <input type="text" name="filtro_item" id="filtro_item" value="<?= htmlspecialchars($filtro_item) ?>" placeholder="Ex: Reformer" list="listaItensDisponiveis" autocomplete="off">
                <datalist id="listaItensDisponiveis">
                    <?php foreach ($itensDisponiveis as $nomeDisponivel): ?>
                        <option value="<?= htmlspecialchars($nomeDisponivel) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="filter-group">
                <label for="filtro_data_ini">Prazo Inicial:</label>
                <input type="date" name="filtro_data_ini" id="filtro_data_ini" value="<?= htmlspecialchars($filtro_data_ini) ?>">
            </div>

            <div class="filter-group">
                <label for="filtro_data_fim">Prazo Final:</label>
                <input type="date" name="filtro_data_fim" id="filtro_data_fim" value="<?= htmlspecialchars($filtro_data_fim) ?>">
            </div>

            <?php
            // Gaiola Cadilac não tem etiqueta de produção física — se o
            // filtro de item já está buscando "gaiola", nem faz sentido
            // oferecer a opção "Apenas PRODUÇÃO".
            $filtroEhGaiola = (stripos($filtro_item, 'gaiola') !== false);
            if ($filtroEhGaiola && $filtro_tipo_eti === 'producao') {
                $filtro_tipo_eti = 'todos';
            }
            ?>
            <div class="filter-group">
                <label for="filtro_tipo_eti">Tipo de Etiqueta:</label>
                <select name="filtro_tipo_eti" id="filtro_tipo_eti">
                    <option value="todos" <?= $filtro_tipo_eti === 'todos' ? 'selected' : '' ?>>Mostrar Ambas</option>
                    <option value="producao" data-oculta-para-gaiola="1" <?= $filtroEhGaiola ? 'style="display:none;" disabled' : '' ?> <?= $filtro_tipo_eti === 'producao' ? 'selected' : '' ?>>Apenas PRODUÇÃO</option>
                    <option value="embalagem" <?= $filtro_tipo_eti === 'embalagem' ? 'selected' : '' ?>>Apenas EMBALAGEM</option>
                </select>
            </div>

            <button type="submit" class="btn-action btn-filtrar">Aplicar Filtros</button>
            <a href="imprimir_etiquetas.php" class="btn-action btn-limpar">Limpar</a>

            <?php if (!empty($itens)): ?>
                <button type="button" id="btnImprimir" class="btn-action btn-print" onclick="prepararImpressao()">Imprimir Selecionadas (<?= count($itens) ?>)</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="no-print" id="modalMotivo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
        <div style="background:#fff; padding:25px; border-radius:8px; max-width:420px; width:90%; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
            <h3 style="margin-top:0; color:#2c3e50;">Reimpressão de Etiqueta</h3>
            <p id="modalMotivoTexto" style="color:#555;"></p>
            <textarea id="inputMotivo" rows="3" style="width:100%; box-sizing:border-box; padding:8px; border:1px solid #ccc; border-radius:5px; font-size:14px;" placeholder="Descreva o motivo da reimpressão..."></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button type="button" class="btn-action btn-limpar" onclick="cancelarReimpressao()">Cancelar</button>
                <button type="button" class="btn-action btn-print" onclick="confirmarReimpressao()">Confirmar e Imprimir</button>
            </div>
        </div>
    </div>

    <div class="container-gabarito">
        <?php
        if (!empty($itens)):
            foreach ($itens as $item):
                $isOS = ($item['tabela_origem'] === 'OS');
                $CodigoBarraBase = $isOS ? 'OS' . $item['id'] : $item['id'];

                // Item já reprovado pela qualidade uma vez: etiqueta de reimpressão
                // leva -PQ/-EQ em vez de -P/-E, pra sinalizar atenção redobrada.
                $jaReprovado = ((int) ($item['qualidade_tentativas'] ?? 0)) > 0;
                $sufixoP = $jaReprovado ? '-PQ' : '-P';
                $sufixoE = $jaReprovado ? '-EQ' : '-E';

                $barcodeFabrica   = base64_encode($generator->getBarcode($CodigoBarraBase . $sufixoP, $generator::TYPE_CODE_128));
                $barcodeEmbalagem = base64_encode($generator->getBarcode($CodigoBarraBase . $sufixoE, $generator::TYPE_CODE_128));

                $corExibir = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? htmlspecialchars($item['cor']) : 'NÃO INFORMADA';

                $nomeEquipamento = trim($item['equipamento']);
                // Gaiola Cadilac não tem etiqueta de produção física — ela já
                // entra "produzida" no sistema (ver atualizar_etapa.php), só
                // imprime a etiqueta de embalagem.
                $apenasEmbalagem = (strcasecmp($nomeEquipamento, 'Carrinho') === 0 || strcasecmp($nomeEquipamento, 'Gaiola') === 0 || strcasecmp($nomeEquipamento, 'Gaiola Cadilac') === 0);

                if (($filtro_tipo_eti === 'todos' || $filtro_tipo_eti === 'producao') && !$apenasEmbalagem):
        ?>
                    <div class="etiqueta etiqueta-fabrica" data-id="<?= (int)$item['id'] ?>" data-origem="<?= htmlspecialchars($item['tabela_origem']) ?>" data-tipo="PRODUCAO">
                        <div class="etiqueta-header">
                            <div class="etiqueta-header-pedido">
                                <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                                <?php if (in_array($item['numero_pedido'], $pedidosMistos)): ?>
                                    <span class="badge-misto-etiqueta">MISTO</span>
                                <?php endif; ?>
                            </div>
                            <span class="tipo-etiqueta">PRODUÇÃO</span>
                        </div>
                        <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                        <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?> | Prazo: <?= htmlspecialchars(substr($item['prazo_producao'], 0, 10))  ?></div>
                        <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                        <div class="barcodeSection">
                            <img src="data:image/png;base64,<?= $barcodeFabrica ?>" class="barcode">
                        </div>
                        <div class="id-text">ID: <?= $item['id'] . $sufixoP ?></div>
                    </div>
                <?php
                endif;

                if ($filtro_tipo_eti === 'todos' || $filtro_tipo_eti === 'embalagem'):
                ?>
                    <div class="etiqueta etiqueta-embalagem" data-id="<?= (int)$item['id'] ?>" data-origem="<?= htmlspecialchars($item['tabela_origem']) ?>" data-tipo="EMBALAGEM">
                        <div class="etiqueta-header">
                            <div class="etiqueta-header-pedido">
                                <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                                <?php if (in_array($item['numero_pedido'], $pedidosMistos)): ?>
                                    <span class="badge-misto-etiqueta">MISTO</span>
                                <?php endif; ?>
                            </div>
                            <span class="tipo-etiqueta">EMBALAGEM</span>
                        </div>
                        <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                        <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?> | Prazo: <?= htmlspecialchars(substr($item['prazo_producao'], 0, 10))  ?></div>
                        <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                        <div class="barcodeSection">
                            <img src="data:image/png;base64,<?= $barcodeEmbalagem ?>" class="barcode">
                        </div>
                        <div class="id-text">ID: <?= $item['id'] . $sufixoE ?></div>
                    </div>
            <?php
                endif;
            endforeach;
        else:
            ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 8px; font-size: 18px; color: #7f8c8d;">
                Nenhum item agendado para este prazo ou com estes filtros.
            </div>
        <?php endif; ?>
    </div>

    <script>
        const csrfToken = <?= json_encode(gerarTokenCSRF()) ?>;

        // Gaiola Cadilac não tem etiqueta de produção física: enquanto o
        // usuário digita "gaiola" no filtro de item, some com a opção
        // "Apenas PRODUÇÃO" e, se ela estava selecionada, volta pra "Mostrar
        // Ambas" — sem precisar recarregar a página.
        (function() {
            const inputItem = document.getElementById('filtro_item');
            const selectTipo = document.getElementById('filtro_tipo_eti');
            const opcaoProducao = selectTipo ? selectTipo.querySelector('option[data-oculta-para-gaiola]') : null;

            if (!inputItem || !selectTipo || !opcaoProducao) return;

            function atualizarOpcaoProducao() {
                const ehGaiola = inputItem.value.toLowerCase().includes('gaiola');
                opcaoProducao.disabled = ehGaiola;
                opcaoProducao.style.display = ehGaiola ? 'none' : '';
                if (ehGaiola && selectTipo.value === 'producao') {
                    selectTipo.value = 'todos';
                }
            }

            inputItem.addEventListener('input', atualizarOpcaoProducao);
            atualizarOpcaoProducao();
        })();

        function coletarItens() {
            return Array.from(document.querySelectorAll('.etiqueta')).map(function(el) {
                return {
                    id_item: el.dataset.id,
                    tabela_origem: el.dataset.origem,
                    tipo_etiqueta: el.dataset.tipo
                };
            });
        }

        function prepararImpressao(motivo) {
            fetch('registrar_impressao_etiqueta.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        itens: coletarItens(),
                        motivo: motivo || '',
                        csrf_token: csrfToken
                    })
                })
                .then(function(resp) {
                    return resp.json();
                })
                .then(function(data) {
                    if (data.success) {
                        window.print();
                    } else if (data.precisa_motivo) {
                        abrirModalMotivo(data.qtd_repetidas);
                    } else {
                        alert('Erro ao registrar impressão: ' + (data.error || 'desconhecido'));
                    }
                })
                .catch(function(err) {
                    alert('Erro ao registrar impressão: ' + err.message);
                });
        }

        function abrirModalMotivo(qtd) {
            document.getElementById('modalMotivoTexto').textContent =
                qtd + ' etiqueta(s) já foram impressas anteriormente. Informe o motivo da reimpressão para continuar:';
            document.getElementById('inputMotivo').value = '';
            document.getElementById('modalMotivo').style.display = 'flex';
        }

        function confirmarReimpressao() {
            var motivo = document.getElementById('inputMotivo').value.trim();
            if (!motivo) {
                alert('Informe o motivo da reimpressão.');
                return;
            }
            document.getElementById('modalMotivo').style.display = 'none';
            prepararImpressao(motivo);
        }

        function cancelarReimpressao() {
            document.getElementById('modalMotivo').style.display = 'none';
        }
    </script>

</body>

</html>