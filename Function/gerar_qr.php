<?php
require_once '../Function/trava.php';
require_once '../global.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

require_once '../config/Database.php';
$database = new Database();
$pdo = $database->getConnection();

$numeroPedido = $_GET['numero'] ?? null;
$equipamento  = $_GET['item'] ?? null;
$posicao      = $_GET['pos'] ?? null;

if (!$numeroPedido || !$equipamento || !$posicao) {
    die("Dados incompletos.");
}

$stmt = $pdo->prepare("SELECT id FROM itens_producao WHERE numero_pedido = ? AND equipamento = ? AND posicao_no_pedido = ?");
$stmt->execute([$numeroPedido, $equipamento, $posicao]);
$peca = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peca) {
    die("Erro: Peça não encontrada no banco.");
}

$id_unico = $peca['id'];

$generator = new BarcodeGeneratorPNG();

$barcodeBase64 = base64_encode($generator->getBarcode($id_unico, $generator::TYPE_CODE_128));

echo "
<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <title>Etiqueta de Código de Barras</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .etiqueta {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 10px solid #db8534;
            width: 350px;
        }
        h1 { color: #333; font-size: 20px; margin-bottom: 5px; text-transform: uppercase; }
        .detalhes { color: #666; font-size: 14px; margin-bottom: 20px; border-bottom: 1px dashed #ddd; padding-bottom: 10px; }
        .barcode-container {
            padding: 20px 10px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .id-text {
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 3px;
        }
        .footer { margin-top: 15px; font-size: 11px; color: #aaa; }
        @media print {
            body { background: none; }
            .etiqueta { box-shadow: none; border: 1px solid #ccc; }
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class='etiqueta'>
        <h1>$equipamento</h1>
        <div class='detalhes'>
            Pedido: <span style='color:#db8534; font-weight:bold;'>#$numeroPedido</span> | Peça: <strong>$posicao</strong>
        </div>
        
        <div class='barcode-container'>
            <img src='data:image/png;base64,$barcodeBase64' style='width: 100%; max-height: 80px;'>
        </div>

        <div class='footer'>Rastreabilidade Equipilates</div>
        <br>
        <button onclick='window.print()' style='cursor:pointer; background:#db8534; color:white; border:none; padding:10px 20px; border-radius:5px;'>Imprimir Etiqueta</button>
    </div>
</body>
</html>
";
