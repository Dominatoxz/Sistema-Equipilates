<?php
require_once '../Services/EGestorService.php';
require_once '../config/Database.php';

$idRegistro = $_GET['id'] ?? null;
$volumesTotais = $_GET['volumes'] ?? null;

$clientId = 'SEU_CLIENT_ID_AQUI';
$clientSecret = 'SEU_CLIENT_SECRET_AQUI';

if (!$idRegistro) {
    die("ID do pedido não fornecido.");
}

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT numero_pedido FROM pedidos_prontos WHERE id = ?");
$stmt->execute([$idRegistro]);
$numeroPedido = $stmt->fetchColumn();

if (!$numeroPedido) {
    die("Pedido não localizado no banco de dados local.");
}

$service = new EgestorService($clientId, $clientSecret);
$resultado = $service->BuscarDadosEtiqueta($numeroPedido); 

if (!$resultado['success']) {
    die("Erro no eGestor: " . htmlspecialchars($resultado['error']));
}

$dados = $resultado['dados'];

if (!$volumesTotais) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Definir Volumes</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 50px; text-align: center; }
            .box { background: white; padding: 30px; display: inline-block; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            input { padding: 10px; width: 80px; font-size: 16px; text-align: center; margin: 15px 0; border: 1px solid #ccc; border-radius: 4px; }
            button { background: #2c3e50; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px; font-weight: bold; }
            button:hover { background: #34495e; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>Quantos volumes (caixas) tem o Pedido <?= htmlspecialchars($numeroPedido) ?>?</h2>
            <form method="GET" action="">
                <input type="hidden" name="id" value="<?= htmlspecialchars($idRegistro) ?>">
                <input type="number" name="volumes" min="1" value="1" required autofocus><br>
                <button type="submit">Gerar Etiquetas</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas de Envio - Pedido <?= htmlspecialchars($dados['numero_pedido']) ?></title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: #525659; }
        
        .etiqueta-container {
            width: 140mm;
            background: white;
            margin: 20px auto;
            padding: 5mm;
            border: 2px dashed #000;
            box-sizing: border-box;
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        .header-title {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 25%;
        }

        .destaque-topo {
            font-size: 16px;
            font-weight: bold;
        }

        .endereco-bloco {
            white-space: pre-wrap;
            height: 60px;
        }

        .alerta-fragil {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            letter-spacing: 1px;
            background-color: #000;
            color: #fff;
            padding: 10px;
        }

        @media print {
            body { background: white; }
            .no-print { display: none; }
            .etiqueta-container { margin: 0; border: none; page-break-after: always; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; padding: 15px; background: #2c3e50; color: white;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; font-weight: bold; background: #2ecc71; color: white; border: none; border-radius: 4px;">🖨️ Imprimir Todas as Etiquetas</button>
    </div>

    <?php 
    for ($i = 1; $i <= $volumesTotais; $i++): 
    ?>
    <div class="etiqueta-container">
        <table>
            <tr>
                <td class="header-title">NÚMERO:</td>
                <td class="destaque-topo"><?= htmlspecialchars($dados['numero_pedido']) ?></td>
                <td class="header-title">Nota Fiscal:</td>
                <td class="destaque-topo"><?= htmlspecialchars($dados['nota_fiscal']) ?></td>
            </tr>
            <tr>
                <td class="header-title">Transportadora:</td>
                <td colspan="3" style="font-weight: bold; font-size: 14px;"><?= htmlspecialchars($dados['transportadora']) ?></td>
            </tr>
            <tr>
                <td class="header-title">Destinatário:</td>
                <td colspan="3" style="font-weight: bold;"><?= htmlspecialchars($dados['destinatario']) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="endereco-bloco"><?= htmlspecialchars($dados['endereco']) ?></td>
            </tr>
            <tr>
                <td class="header-title">Volume Parcial:</td>
                <td style="font-weight: bold; text-align: center; font-size: 15px;"><?= $i ?></td>
                <td class="header-title">Volume Total:</td>
                <td style="font-weight: bold; text-align: center; font-size: 15px;"><?= htmlspecialchars($volumesTotais) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="alerta-fragil">EQUIPAMENTO FRÁGIL! CUIDADO!</td>
            </tr>
        </table>
    </div>
    <?php endfor; ?>

</body>
</html>