<?php
require_once '../Function/trava.php'; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Central de Produção</title>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fafaf8;
            color: #343a40;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: opacity 0.25s ease-in-out;
            opacity: 1;
        }

        body.fade-out {
        opacity: 0 !important;
        }

        .container {
            text-align: center;
            max-width: 950px;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        .header-painel { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            width: 100%;
            max-width: 910px;
            padding: 10px 20px;
            box-sizing: border-box;
        }

        .header-painel img {
            max-height: 50px;
            width: auto;
        }

        .header-painel a {
            color: #e67e22;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.95rem;
            border: 1px solid #e67e22;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
            background-color: rgba(230, 126, 34, 0.03);
        }

        .header-painel a:hover {
            background-color: #e67e22;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.15);
        }

        h1 {
            font-size: 2.3rem;
            color: #212529;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 1.2rem;
            margin-top: 0;
            margin-bottom: 12px;
            color: #e67e22;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        p.subtitle {
            font-size: 0.95rem;
            color: #495057;  
            font-style: italic;
            max-width: 700px;
            margin: 0 auto 5px auto;
            line-height: 1.4;
        }

        p.sub-subtitle {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .grid-painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            padding: 10px;
            width: 100%;
        }

        .card-botao {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            padding: 30px 20px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }

        .card-botao h3 {
            margin: 15px 0 8px 0;
            font-size: 1.25rem;
            color: #212529;
            transition: color 0.3s ease;
        }

        .card-botao p {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
            line-height: 1.4;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 5px;
            transition: transform 0.3s ease;
        }

        .card-botao:hover {
            transform: translateY(-5px);
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .card-botao:hover .icon-box {
            transform: scale(1.1);
        }

   
        .prod .icon-box { background-color: #eaf2f8; color: #3498db; }
        .prod:hover { border-color: #3498db; }
        .prod:hover h3 { color: #3498db; }

        .os-eq .icon-box { background-color: #fdf2e9; color: #e67e22; }
        .os-eq:hover { border-color: #e67e22; }
        .os-eq:hover h3 { color: #e67e22; }

        .os-ac .icon-box { background-color: #f5eef8; color: #9b59b6; }
        .os-ac:hover { border-color: #9b59b6; }
        .os-ac:hover h3 { color: #9b59b6; }

        .pos-venda .icon-box { background-color: #e8f8f5; color: #2ecc71; }
        .pos-venda:hover { border-color: #2ecc71; }
        .pos-venda:hover h3 { color: #2ecc71; }

        .footer {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #adb5bd;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="header-painel">
        <img src="../assets/logo_equipilates.png" alt="">
        <a href="../index.php">Central ></a>
    </div>
    <div class="container">
        <h1>Central de controle</h1>
        <h2>Contemporâneo</h2>
        <p class="subtitle">"A mente, quando habitualmente ocupada, esculpe o corpo, dita a postura e define os movimentos."</p>
        <p class="sub-subtitle">- Joseph Pilates</p>

        <div class="grid-painel">
            <a href="tabela.php" class="card-botao prod">
                <div class="icon-box">📦</div>
                <h3>Equipamentos</h3>
                <p>Equipamentos da linha conteporânea e pedidos normais</p>
            </a>

            <a href="tabela_acessorios.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha conteporânea e pedidos normais</p>
            </a>

            <a href="tabela_os.php" class="card-botao os-eq">
                <div class="icon-box">🛠️</div>
                <h3>Equipamentos OS</h3>
                <p>Estruturas de Ordens de Serviço</p>
            </a>

            <a href="tabela_os_acess.php" class="card-botao os-ac">
                <div class="icon-box">⚙️</div>
                <h3>Acessórios OS</h3>
                <p>Molas, Caixas e Pranchas de OS</p>
            </a>

            <a href="tabela_controle_producao.php" class="card-botao pos-venda">
                <div class="icon-box">📋</div>
                <h3>Controle da Produção</h3>
                <p>Fila da produção dos pedidos</p>
            </a>

            <a href="tabela_financeiro.php" class="card-botao pos-venda">
                <div class="icon-box">💰</div>
                <h3>Financeiro</h3>
                <p>Fila para o controle do financeiro</p>
            </a>

            <a href="tabela_posVenda.php" class="card-botao pos-venda">
                <div class="icon-box">✅</div>
                <h3>Pós-Venda</h3>
                <p>Fila de pedidos produzidos</p>
            </a>
            
            <a href="tabela_expedição.php" class="card-botao pos-venda">
                <div class="icon-box">✈️</div>
                <h3>Expedição</h3>
                <p>Fila de organização de pedidos para expedição</p>
            </a>

            <a href="tabela_controle.php" class="card-botao pos-venda">
                <div class="icon-box">🎮</div>
                <h3>Controle</h3>
                <p>Fila de controle de pedidos</p>
            </a>

            <a href="../Function/imprimir_etiquetas.php" class="card-botao pos-venda">
                <div class="icon-box">🖨️</div>
                <h3>Impressão</h3>
                <p>Etiquetas organizadas</p>
            </a>

        </div>
    </div>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>

    <script>
        document.querySelector('.btn-voltar').addEventListener('click', function(e) {
            e.preventDefault();
            const destino = this.getAttribute('href');
            document.body.classList.add('fade-out');
            setTimeout(() => {
                window.location.href = destino;
            }, 300);
        });
    </script>
</body>
</html>