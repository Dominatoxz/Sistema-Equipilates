<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do QR Code</title>
    <link rel="stylesheet" href="visualizacao.css">
</head>
<body>
    <h1>Informações do QR Code</h1>
    <p><strong>Número do Pedido:</strong> <?php echo htmlspecialchars($_GET['numero'] ?? 'Não informado'); ?></p>
    <p><strong>Item:</strong> Reformer</p>
    <p><strong>Cor:</strong> Caramelo</p>
    <p><strong>Quantidade:</strong> 2</p>
</body>
</html>