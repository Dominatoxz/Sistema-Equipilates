<?php
require_once '../Function/trava.php'; 
require_once '../vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;
require_once '../config/Database.php';

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
            font-size: 18px;
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

        .etiqueta-sub, .etiqueta-cor {
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
            .no-print { display: none; }
            body { background: white; }
            .container-gabarito { padding: 0; margin: 0; }
            .etiqueta { 
                margin: 0; 
                border: none; 
            }
            .etiqueta-fabrica { border-left: 6mm solid #db8534 !important; } 
            .etiqueta-embalagem { border-left: 6mm solid #27ae60 !important; } 
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