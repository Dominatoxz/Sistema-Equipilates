<?php
require_once './vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once './config/Database.php';
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
    die("Erro: Esta peça ainda não foi processada no banco de produção. Execute o CALL gerar_unidades_producao();");
}

$id_unico = $peca['id'];
$meuIp = "10.0.0.127";
$urlParaCelular = "http://$meuIp:8000/View/visualizar.php?id=$id_unico";

$options = new QROptions(['outputType' => 'fpm-png', 'imageBase64' => true]);
$qrBase64 = (new QRCode($options))->render($urlParaCelular);

echo "
<!DOCTYPE html>
<html lang='pt-br'>
<title>Informações do QR Code</title>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            border-top: 10px solid #db8534; /* Cor laranja da sua marca */
            width: 320px;
        }
        h1 {
            color: #333;
            font-size: 22px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .detalhes {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ddd;
        }
        .pedido-badge {
            font-weight: bold;
            color: #db8534;
        }
        .qr-container {
            background: #fff;
            padding: 10px;
            display: inline-block;
            border: 1px solid #eee;
            border-radius: 10px;
        }
        .footer {
            margin-top: 15px;
            font-size: 11px;
            color: #aaa;
            text-transform: uppercase;
        }
        @media print {
            body { background: none; }
            .etiqueta { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class='etiqueta'>
        <h1>$equipamento</h1>
        <div class='detalhes'>
            Pedido: <span class='pedido-badge'>#$numeroPedido</span> | Peça: <strong>$posicao</strong>
        </div>
        <div class='qr-container'>
            <img src='$qrBase64' style='width:220px; display:block;'>
        </div>
        <div class='footer'>
            Rastreabilidade Equipilates
        </div>
        <br>
        <button onclick='window.print()' style='cursor:pointer; background:#db8534; color:white; border:none; padding:8px 15px; border-radius:5px;'>Imprimir</button>
    </div>
</body>
</html>
";