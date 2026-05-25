<?php
require_once './vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;
require_once './config/Database.php';

$db = (new Database())->getConnection();
$generator = new BarcodeGeneratorPNG();

$query = "SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor 
          FROM itens_producao
          WHERE status != 'Embalado' 
            AND equipamento NOT LIKE 'Emb.%' 
          
          UNION ALL
          
          SELECT id, numero_pedido, equipamento, posicao_no_pedido, cor 
          FROM itens_os
          WHERE status != 'Embalado' 
            AND equipamento NOT LIKE 'Emb.%' 
         
          ORDER BY numero_pedido ASC, id ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$itens) {
    die("Não há itens no quadro para gerar etiquetas.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Impressão de Etiquetas Separadas (Fábrica / Embalagem)</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .no-print {
            background: #fff;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #db8534;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-print {
            padding: 12px 30px;
            background: #53a340;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }

        .container-gabarito {
            max-width: 210mm;
            margin: 20px auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 8mm;
            padding: 10px;
        }

        .etiqueta {
            background: white;
            border: 1px dashed #333;
            padding: 10px;
            box-sizing: border-box;
            page-break-inside: avoid;
            height: 50mm;
            width: 90mm;
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .tipo-etiqueta{
            font-size: 20px;

        }
        .etiqueta-fabrica {
            border-left: 8px solid #000000;
        }

        .etiqueta-embalagem {
            border-left: 8px solid #000000;
        }

        .etiqueta-header {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: bold;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        .tipo-etiqueta {
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .etiqueta-titulo {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            color: #000;
        }

        .num-pedido{
            font-size: 20px;
        }

        .etiqueta-sub {
            font-size: 12px;
            color: #333;
        }

        .etiqueta-cor {
            font-weight: bold;
            font-size: 12px;
            color: #333;
        }

        .barcode {
            width: 70%;
            height: 40px;
            margin: 4px 0;
        }

        .barcodeSection {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 5px;
        }   

        .id-text {
            font-family: monospace;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; }
            .container-gabarito { margin: 0; padding: 0; }
            .etiqueta { border: 1px solid #000; }
            .etiqueta-fabrica { border-left: 8px solid #db8534; }
            .etiqueta-embalagem { border-left: 8px solid #27ae60; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <h2>Gerador de Etiquetas Duplas (Produção + Embalagem)</h2>
        <button class="btn-print" onclick="window.print()">Imprimir Todas as Etiquetas</button>
    </div>

    <div class="container-gabarito">
        <?php foreach ($itens as $item): 
            $isOS = (stripos($item['numero_pedido'], 'os') !== false);
            $titulo = $isOS ? "[OS]" : "";

            $CodigoBarraBase = $isOS ? 'OS' . $item['id'] : $item['id'];

            $barcodeFabrica   = base64_encode($generator->getBarcode($CodigoBarraBase . '-P', $generator::TYPE_CODE_128));
            $barcodeEmbalagem = base64_encode($generator->getBarcode($CodigoBarraBase . '-E', $generator::TYPE_CODE_128));

            $corExibir = (!empty($item['cor']) && $item['cor'] !== 'COD. COR') ? htmlspecialchars($item['cor']) : 'NÃO INFORMADA';
        ?>
                    
            <div class="etiqueta etiqueta-fabrica">
                <div class="etiqueta-header">
                    <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                    <span class="tipo-etiqueta">PRODUÇÃO</span>
                </div>
                <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?></div>
                <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                <div class="barcodeSection">
                    <img src="data:image/png;base64,<?= $barcodeFabrica ?>" class="barcode">
                </div>
                <div class="id-text">ID: <?= $item['id'] ?>-P</div>
            </div>

            <div class="etiqueta etiqueta-embalagem">
                <div class="etiqueta-header">
                    <span class="num-pedido">PEDIDO #<?= htmlspecialchars($item['numero_pedido']) ?></span>
                    <span class="tipo-etiqueta">EMBALAGEM</span>
                </div>
                <div class="etiqueta-titulo"><?= htmlspecialchars($item['equipamento']) ?></div>
                <div class="etiqueta-sub">Peça: <?= htmlspecialchars($item['posicao_no_pedido']) ?></div>
                <div class="etiqueta-cor">Cor: <?= htmlspecialchars($corExibir) ?></div>
                <div class="barcodeSection">
                    <img src="data:image/png;base64,<?= $barcodeEmbalagem ?>" class="barcode">
                </div>
                <div class="id-text">ID: <?= $item['id'] ?>-E</div>
            </div>

        <?php endforeach; ?>
    </div>

</body>
</html>