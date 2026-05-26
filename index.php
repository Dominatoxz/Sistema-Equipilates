<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Central de Produção</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            text-align: center;
            max-width: 900px;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2{
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #e67e22;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        p.subtitle {
            font-size: 0.9rem;
            color: #7f8c8d;  
        }

        p.sub-subtitle{
            font-size: 0.7rem;
            color: #7f8c8d;
            margin-bottom: 40px;
        }

        .header-painel { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            width: 1500px;
            padding: 10px;
        }

        .header-painel a {
            color: #e67e22;
            text-decoration: none;
        }

        .grid-painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 10px;
            width: 900px;
        }

        .card-botao {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 30px 20px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .prod:hover {
            border-color: #3498db;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.2);
        }
        .prod .icon-box { background-color: #eaf2f8; color: #3498db; }

        .os-eq:hover {
            border-color: #e67e22;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(230, 126, 34, 0.2);
        }
        .os-eq .icon-box { background-color: #fdf2e9; color: #e67e22; }

        .os-ac:hover {
            border-color: #9b59b6;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(155, 89, 182, 0.2);
        }
        .os-ac .icon-box { background-color: #f5eef8; color: #9b59b6; }

        .pos-venda:hover {
            border-color: #2ecc71;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.2);
        }
        .pos-venda .icon-box { background-color: #e8f8f5; color: #2ecc71; }


        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .card-botao:hover .icon-box {
            transform: scale(1.1);
        }

        .card-botao h3 {
            margin: 10px 0 5px 0;
            font-size: 1.3rem;
            color: #2c3e50;
        }

        .card-botao p {
            margin: 0;
            font-size: 0.9rem;
            color: #95a5a6;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="header-painel">
        <img src="assets/logo_equipilates.png" alt="">
        <a href="">Linha Clássica ></a>
    </div>
    <div class="container">
        <h1>Central de controle</h1>
        <h2>Contemporâneo</h2>
        <p class="subtitle">"A mente, quando habitualmente ocupada, esculpe o corpo, dita a postura e define os movimentos."</p>
        <p class="sub-subtitle">- Joseph Pilates</p>

        <div class="grid-painel">
            <a href="View/tabela.php" class="card-botao prod">
                <div class="icon-box">📦</div>
                <h3>Equipamentos</h3>
                <p>Equipamentos da linha conteporânea e pedidos normais</p>
            </a>

            <a href="View/tabela_acessorios.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha conteporânea e pedidos normais</p>
            </a>

            <a href="View/tabela_os.php" class="card-botao os-eq">
                <div class="icon-box">🛠️</div>
                <h3>Equipamentos OS</h3>
                <p>Estruturas de Ordens de Serviço</p>
            </a>

            <a href="View/tabela_os_acess.php" class="card-botao os-ac">
                <div class="icon-box">⚙️</div>
                <h3>Acessórios OS</h3>
                <p>Molas, Caixas e Pranchas de OS</p>
            </a>

            <a href="View/tabela_posVenda.php" class="card-botao pos-venda">
                <div class="icon-box">✅</div>
                <h3>Pós-Venda</h3>
                <p>Fila de pedidos produzidos</p>
            </a>

            <a href="View/tabela_financeiro.php" class="card-botao pos-venda">
                <div class="icon-box">💰</div>
                <h3>Financeiro</h3>
                <p>Fila para o controle do financeiro</p>
            </a>
            
            <a href="View/tabela_expedição.php" class="card-botao pos-venda">
                <div class="icon-box">✈️</div>
                <h3>Expedição</h3>
                <p>Fila de organização de pedidos para expedição</p>
            </a>

            <a href="imprimir_etiquetas.php" class="card-botao pos-venda">
                <div class="icon-box">🖨️</div>
                <h3>Impressão</h3>
                <p>Etiquetas organizadas</p>
            </a>

        </div>
    </div>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>

</body>
</html>