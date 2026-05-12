<?php
while (ob_get_level()) { ob_end_clean(); }

require_once __DIR__ . '/./vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$numero = $_GET['numero'] ?? '';

if (empty($numero)) {
    die("Erro: Número do pedido não informado.");
}

$meuIpLocal = "10.0.0.127"; 
$porta      = "8000"; 
$urlDestino = "http://$meuIpLocal:$porta/View/visualizarInformações.php?numero=" . $numero;


$options = new QROptions([
    'version'      => 6,
    'outputType'   => 'fpm-png', 
    'imageBase64'  => true,
    'scale'        => 10,
    'addQuietzone' => true, 
    'quietzoneSize'=> 4,
]);

try {
    $base64Image = (new QRCode($options))->render($urlDestino);

    echo "<!DOCTYPE html>
    <html lang='pt'>
    <head><meta charset='UTF-8'><title>QR Code - Pedido $numero</title></head>
    <body style='font-family:sans-serif; text-align:center; padding-top:50px; background:#f4f4f9;'>
        <div style='display:inline-block; background:white; padding:30px; border-radius:15px; box-shadow:0 4px 10px rgba(0,0,0,0.1);'>
            <h2 style='margin-bottom:10px;'>QR Code de Acesso</h2>
            <p style='color:#666;'>Escaneie para ver os detalhes do Pedido: <strong>$numero</strong></p>
            
            <img src='$base64Image' style='border:1px solid #eee;' />
            
            <br>
            <button onclick='window.print()' style='padding:10px 20px; margin-top:20px; cursor:pointer;'>Imprimir</button>
        </div>
    </body>
    </html>";

} catch (Exception $e) {
    die("Erro ao gerar QR Code: " . $e->getMessage());
}