<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadro</title>
</head>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7f6;
    color: #333;
    padding: 20px;
}

h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
}

table {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    border-collapse: collapse; 
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
    border-radius: 8px;
    overflow: hidden; 
}

th {
    background-color: #db8534;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 15px;
    text-align: left;
}

td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}


tr:nth-child(even) {
    background-color: #f9f9f9;
}


tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
}


td:first-child {
    font-weight: bold;
    color: #db8534;
    width: 80px;
}
</style>
<body>
    <h1>Dados da Planilha</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID-Pedido</th>
                <th>Número</th>
                <th>Cor</th>
                <th>Prazo de Produção</th>
            </tr>
        </thead>
        <tbody>
            <?php $pedidos = $pedidos ?? []; ?>
            <?php foreach($pedidos as $pedido): ?>
            <tr>
                <td><?= htmlspecialchars($pedido['id_pedido']); ?></td>
                <td><a href=""><?= htmlspecialchars($pedido['num_pedido']); ?></a></td>
                <td><?= htmlspecialchars($pedido['codigo_cor']); ?></td>
                <td><?= htmlspecialchars($pedido['prazo_producao']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
