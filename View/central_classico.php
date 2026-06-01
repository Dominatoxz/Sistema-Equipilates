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
            background-color: #121214;
            color: #e1e1e6;
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
            color: #f39c12;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.05rem;
            border: 1px solid #f39c12;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
            background-color: rgba(243, 156, 18, 0.05);
        }

        .header-painel a:hover {
            background-color: #f39c12;
            color: #121214;
            box-shadow: 0 0 15px rgba(243, 156, 18, 0.3);
        }

        h1 {
            font-size: 2.5rem;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 1.3rem;
            margin-top: 0;
            margin-bottom: 15px;
            color: #f39c12;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        p.subtitle {
            font-size: 1rem;
            color: #a9a9b3;  
            font-style: italic;
            max-width: 700px;
            margin: 0 auto 5px auto;
            line-height: 1.4;
        }

        p.sub-subtitle {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 0;
            margin-bottom: 40px;
            font-weight: bold;
        }

        .grid-painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            padding: 10px;
            width: 100%;
        }

        .card-botao {
            background: #1e1e24;
            border-radius: 12px;
            border: 1px solid #2a2a35;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            padding: 30px 20px;
            text-decoration: none;
            color: #e1e1e6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
        }

        .card-botao h3 {
            margin: 15px 0 8px 0;
            font-size: 1.3rem;
            color: #ffffff;
            transition: color 0.3s ease;
        }

        .card-botao p {
            margin: 0;
            font-size: 0.9rem;
            color: #a9a9b3;
            text-align: center;
            line-height: 1.3;
            transition: color 0.3s ease;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            background-color: #2a2a35;
        }

        .card-botao:hover {
            transform: translateY(-5px);
            background: #25252e;
        }

        .card-botao:hover .icon-box {
            transform: scale(1.1);
        }

        
        .prod .icon-box { color: #3498db; background-color: rgba(52, 152, 219, 0.1); }
        .prod:hover { border-color: #3498db; box-shadow: 0 8px 25px rgba(52, 152, 219, 0.25); }
        .prod:hover h3 { color: #3498db; }

        .os-eq .icon-box { color: #e67e22; background-color: rgba(230, 126, 34, 0.1); }
        .os-eq:hover { border-color: #e67e22; box-shadow: 0 8px 25px rgba(230, 126, 34, 0.25); }
        .os-eq:hover h3 { color: #e67e22; }

        .os-ac .icon-box { color: #9b59b6; background-color: rgba(155, 89, 182, 0.1); }
        .os-ac:hover { border-color: #9b59b6; box-shadow: 0 8px 25px rgba(155, 89, 182, 0.25); }
        .os-ac:hover h3 { color: #9b59b6; }

        .pos-venda .icon-box { color: #2ecc71; background-color: rgba(46, 204, 113, 0.1); }
        .pos-venda:hover { border-color: #2ecc71; box-shadow: 0 8px 25px rgba(46, 204, 113, 0.25); }
        .pos-venda:hover h3 { color: #2ecc71; }

        .footer {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #57606f;
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
        <h2>Clássico</h2>
        <p class="subtitle">"É estar presente, concentrado e não distraído. É a mente que esculpe o corpo."</p>
        <p class="sub-subtitle">- Joseph Pilates</p>

        <div class="grid-painel">
            <a href="tabela_classico.php" class="card-botao prod">
                <div class="icon-box">📦</div>
                <h3>Equipamentos</h3>
                <p>Equipamentos da linha clássica e pedidos normais</p>
            </a>

            <a href="tabela_classico_segunda.php" class="card-botao prod">
                <div class="icon-box">📦</div>
                <h3>Equipamentos</h3>
                <p>Equipamentos da linha clássica e pedidos normais parte 2</p>
            </a>

            <a href="tabela_acessorios_classico.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha clássica e pedidos normais</p>
            </a>

            <a href="tabela_acessorios_classico_segunda.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha clássica e pedidos normais Parte 2</p>
            </a>

            <a href="tabela_acessorios_classico_terceira.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha clássica e pedidos normais Parte 3</p>
            </a>

            <a href="tabela_os.php" class="card-botao os-eq">
                <div class="icon-box">🛠️</div>
                <h3>Equipamentos OS</h3>
                <p>Estruturas de Ordens de Serviço</p>
            </a>

            <a href="tabela_os_acess.php" class="card-botao os-ac">
                <div class="icon-box">⚙️</div>
                <h3>Acessórios OS</h3>
                <p>Acessórios de OS</p>
            </a>

            <a href="tabela_posVenda.php" class="card-botao pos-venda">
                <div class="icon-box">✅</div>
                <h3>Pós-Venda</h3>
                <p>Fila de pedidos produzidos</p>
            </a>

            <a href="tabela_financeiro.php" class="card-botao pos-venda">
                <div class="icon-box">💰</div>
                <h3>Financeiro</h3>
                <p>Fila para o controle do financeiro</p>
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