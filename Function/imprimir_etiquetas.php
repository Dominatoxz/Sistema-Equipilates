<?php
require_once '../Function/trava.php';
require_once '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

require_once '../config/Database.php';

$db = (new Database())->getConnection();
$generator = new BarcodeGeneratorPNG();

$filtro_pedido    = isset($_GET['filtro_pedido']) ? trim($_GET['filtro_pedido']) : '';
$filtro_item      = isset($_GET['filtro_item']) ? trim($_GET['filtro_item']) : '';
$filtro_tipo_eti  = isset($_GET['filtro_tipo_eti']) ? trim($_GET['filtro_tipo_eti']) : 'todos';
$filtro_data      = isset($_GET['filtro_data']) ? trim($_GET['filtro_data']) : '';

$params = [];

$sql_producao = "SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'PRODUCAO' AS tabela_origem 
                 FROM itens_producao
                 WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'";

if (!empty($filtro_pedido)) {
    $sql_producao .= " AND numero_pedido LIKE :pedido1";
    $params[':pedido1'] = '%' . $filtro_pedido . '%';
}
if (!empty($filtro_item)) {
    $sql_producao .= " AND equipamento LIKE :item1";
    $params[':item1'] = '%' . $filtro_item . '%';
}
if (!empty($filtro_data)) {
    $sql_producao .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') = :data1";
    $params[':data1'] = $filtro_data;
}

$sql_os = "SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor, prazo_producao, 'OS' AS tabela_origem 
           FROM itens_os
           WHERE status != 'Embalado' AND equipamento NOT LIKE 'Emb.%'";

if (!empty($filtro_pedido)) {
    $sql_os .= " AND numero_pedido LIKE :pedido2";
    $params[':pedido2'] = '%' . $filtro_pedido . '%';
}
if (!empty($filtro_item)) {
    $sql_os .= " AND equipamento LIKE :item2";
    $params[':item2'] = '%' . $filtro_item . '%';
}
if (!empty($filtro_data)) {
    $sql_os .= " AND STR_TO_DATE(prazo_producao, '%d/%m/%Y') = :data2";
    $params[':data2'] = $filtro_data;
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
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        .num-pedido {
            font-size: 22px;
            font-weight: bold;
            color: #000;
        }

        .tipo-etiqueta {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
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
            <a href="../View/central_contemporaneo.php" class="btn-action btn-voltar">← Voltar para a Central</a>
        </div>

        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="filtro_pedido">Nº do Pedido / OS:</label>
                <input type="text" name="filtro_pedido" id="filtro_pedido" value="<?= htmlspecialchars($filtro_pedido) ?>" placeholder="Ex: 4806">
            </div>

            <div class="filter-group">
                <label for="filtro_item">Nome do Item:</label>
                <input type="text" name="filtro_item" id="filtro_item" value="<?= htmlspecialchars($filtro_item) ?>" placeholder="Ex: Reformer">
            </div>

            <div class="filter-group">
                <label for="filtro_data">Prazo de Produção:</label>
                <input type="date" name="filtro_data" id="filtro_data" value="<?= htmlspecialchars($filtro_data) ?>">
            </div>

            <div class="filter-group">
                <label for="filtro_tipo_eti">Tipo de Etiqueta:</label>
                <select name="filtro_tipo_eti" id="filtro_tipo_eti">
                    <option value="todos" <?= $filtro_tipo_eti === 'todos' ? 'selected' : '' ?>>Mostrar Ambas</option>
                    <option value="producao" <?= $filtro_tipo_eti === 'producao' ? 'selected' : '' ?>>Apenas PRODUÇÃO</option>
                    <option value="embalagem" <?= $filtro_tipo_eti === 'embalagem' ? 'selected' : '' ?>>Apenas EMBALAGEM</option>
                </select>
            </div>

            <button type="submit" class="btn-action btn-filtrar">Aplicar Filtros</button>
            <a href="imprimir_etiquetas.php" class="btn-action btn-limpar">Limpar</a>

            <?php if (!empty($itens)): ?>
                <button type="button" class="btn-action btn-print" onclick="window.print()">Imprimir Selecionadas (<?= count($itens) ?>)</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="container-gabarito">
        <?php
        if (!empty($itens)):
            foreach ($itens as $item):
                $isOS = ($item['tabela_origem'] === 'OS');
                $CodigoBarraBase = $isOS ? 'OS' . $item['id'] : $item['id'];

                $barcodeFabrica   = base64_encode($generator->getBarcode($CodigoBarraBase . '-P', $generator::TYPE_CODE_128));
                $barcodeEmbalagem = base64_encode($generator->getBarcode($CodigoBarraBase . '-E', $generator::TYPE_CODE_128));

                $corExibir = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? htmlspecialchars($item['cor']) : 'NÃO INFORMADA';

                $nomeEquipamento = trim($item['equipamento']);
                $apenasEmbalagem = (strcasecmp($nomeEquipamento, 'Carrinho') === 0 || strcasecmp($nomeEquipamento, 'Gaiola') === 0);

                if (($filtro_tipo_eti === 'todos' || $filtro_tipo_eti === 'producao') && !$apenasEmbalagem):
        ?>
                    <div class="etiqueta etiqueta-fabrica">
                        <div class="etiqueta-header">
                            <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                            <span class="tipo-etiqueta">PRODUÇÃO</span>
                        </div>
                        <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                        <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?> | Prazo: <?= htmlspecialchars(substr($item['prazo_producao'], 0, 10))  ?></div>
                        <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                        <div class="barcodeSection">
                            <img src="data:image/png;base64,<?= $barcodeFabrica ?>" class="barcode">
                        </div>
                        <div class="id-text">ID: <?= $item['id'] ?>-P</div>
                    </div>
                <?php
                endif;

                if ($filtro_tipo_eti === 'todos' || $filtro_tipo_eti === 'embalagem'):
                ?>
                    <div class="etiqueta etiqueta-embalagem">
                        <div class="etiqueta-header">
                            <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                            <span class="tipo-etiqueta">EMBALAGEM</span>
                        </div>
                        <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                        <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?> | Prazo: <?= htmlspecialchars(substr($item['prazo_producao'], 0, 10))  ?></div>
                        <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                        <div class="barcodeSection">
                            <img src="data:image/png;base64,<?= $barcodeEmbalagem ?>" class="barcode">
                        </div>
                        <div class="id-text">ID: <?= $item['id'] ?>-E</div>
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

</body>

</html>