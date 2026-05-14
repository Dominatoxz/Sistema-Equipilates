<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações da Peça</title>
    <link rel="stylesheet" href="css/visualizacao.css">
</head>
<body>
    <?php
    require_once '../config/Database.php';
    $database = new Database();
    $db = $database->getConnection();

    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("SELECT * FROM itens_producao WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>

    <div class="container-geral">
        <h1>Informações da Peça</h1>
        <?php if ($item): ?>
            <p><strong>ID da Peça:</strong> <?= $item['id'] ?></p>
            <p><strong>Pedido:</strong> <?= $item['numero_pedido'] ?></p>
            <p><strong>Equipamento:</strong> <?= $item['equipamento'] ?></p>
            <p><strong>Status:</strong> <?= $item['status'] ?></p>
        <?php else: ?>
            <p>Peça não encontrada.</p>
        <?php endif; ?>
    </div>
</body>
</html>
