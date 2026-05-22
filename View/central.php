<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Controle - Produção</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        h1 { color: #2c3e50; margin-bottom: 10px; font-size: 32px; text-transform: uppercase; }
        p.subtitle { color: #7f8c8d; margin-bottom: 40px; font-size: 18px; }
        .menu-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; width: 100%; max-width: 1000px; }
        .menu-card {
            background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 30px; text-align: center; text-decoration: none; display: flex;
            flex-direction: column; align-items: center; justify-content: space-between;
            border: 2px solid transparent; transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
        }
        .card-producao:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(52, 152, 219, 0.2); border-color: #3498db; }
        .card-acessorios:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(155, 89, 182, 0.2); border-color: #9b59b6; }
        .card-posvenda:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(46, 204, 113, 0.2); border-color: #2ecc71; }
        .card-icon { font-size: 45px; margin-bottom: 15px; }
        .card-title { font-size: 22px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
        .card-desc { font-size: 14px; color: #7f8c8d; line-height: 1.5; }
        footer { margin-top: 50px; font-size: 12px; color: #bdc3c7; }
    </style>
</head>
<body>

    <h1>Central de Fábrica</h1>
    <p class="subtitle">Selecione o painel que deseja monitorar ou operar</p>

    <div class="menu-container">

        <a href="index.php?action=itens" class="menu-card card-producao">
            <div class="card-icon">🏗️</div>
            <div class="card-title">Quadro de Equipamentos</div>
            <div class="card-desc">Gerenciamento de montagem e embalagem dos aparelhos principais (Reformer, Cadilac, etc).</div>
        </a>

        <a href="index.php?action=acessorios" class="menu-card card-acessorios">
            <div class="card-icon">📦</div>
            <div class="card-title">Quadro de Acessórios</div>
            <div class="card-desc">Controle de produção das caixas, pranchas e pacotes de molas separados por pedido.</div>
        </a>

        <a href="index.php?action=posvenda" class="menu-card card-posvenda">
            <div class="card-icon">🚚</div>
            <div class="card-title">Fila de Pós-Venda</div>
            <div class="card-desc">Pedidos 100% embalados aguardando faturamento, expedição ou despacho final.</div>
        </a>

    </div>

    <footer>Sistema de Monitoramento Interno © 2026</footer>

</body>
</html>