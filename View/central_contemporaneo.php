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
        :root {
            --bg-color: #f4f6f9;
            --bg-gradient: radial-gradient(circle at 50% 50%, #ffffff 0%, #e9edf3 100%);
            --card-bg: rgba(255, 255, 255, 0.7);
            --card-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e1e26;
            --text-muted: #62627a;

            --color-gold: #d97706;
            --color-prod: #0088cc;
            --color-oseq: #e65100;
            --color-osac: #9c27b0;
            --color-pos: #10b981;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-color);
            background: var(--bg-gradient);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            overflow-x: hidden;
        }

        body.fade-out {
            opacity: 0 !important;
        }

        .container {
            text-align: center;
            max-width: 1100px;
            width: 100%;
            padding: 40px 20px;
            box-sizing: border-box;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .header-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            width: 100%;
            max-width: 1060px;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .header-painel img {
            max-height: 120px;
            width: 180px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.05));
        }

        .header-painel a.btn-voltar {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .header-painel a.btn-voltar:hover {
            background: #1e1e26;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transform: scale(1.05);
            border-color: #1e1e26;
        }

        h1 {
            font-size: 2.8rem;
            color: #1e1e26;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 5px;
            font-weight: 800;
            background: linear-gradient(180deg, #111115 0%, #4a4a6a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            font-size: 1.1rem;
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--color-gold);
            text-transform: uppercase;
            letter-spacing: 6px;
            font-weight: 700;
        }

        p.subtitle {
            font-size: 1.05rem;
            color: var(--text-muted);
            max-width: 650px;
            margin: 0 auto 8px auto;
            line-height: 1.6;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        p.sub-subtitle {
            font-size: 0.75rem;
            color: #9292a6;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-top: 0;
            margin-bottom: 50px;
            font-weight: 700;
        }

        .grid-painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 10px 0;
            width: 100%;
        }

        .card-botao {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 35px 25px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02), inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .card-botao::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-25deg);
            transition: 0.75s;
        }

        .card-botao:hover::before {
            left: 125%;
        }

        .card-botao h3 {
            margin: 18px 0 8px 0;
            font-size: 1.25rem;
            color: #1e1e26;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .card-botao p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
            font-weight: 500;
        }

        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.03);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }

        .card-botao:hover {
            transform: translateY(-5px);
            background: #ffffff;
            border-color: rgba(0, 0, 0, 0.08);
        }

        .card-botao:hover .icon-box {
            transform: scale(1.1) translateY(-2px);
        }

        .prod .icon-box {
            color: var(--color-prod);
            background: rgba(0, 136, 204, 0.06);
        }

        .prod:hover {
            box-shadow: 0 15px 30px rgba(0, 136, 204, 0.12);
            border-color: rgba(0, 136, 204, 0.3);
        }

        .prod:hover h3 {
            color: var(--color-prod);
        }

        .os-eq .icon-box {
            color: var(--color-oseq);
            background: rgba(230, 81, 0, 0.06);
        }

        .os-eq:hover {
            box-shadow: 0 15px 30px rgba(230, 81, 0, 0.12);
            border-color: rgba(230, 81, 0, 0.3);
        }

        .os-eq:hover h3 {
            color: var(--color-oseq);
        }

        .os-ac .icon-box {
            color: var(--color-osac);
            background: rgba(156, 39, 176, 0.06);
        }

        .os-ac:hover {
            box-shadow: 0 15px 30px rgba(156, 39, 176, 0.12);
            border-color: rgba(156, 39, 176, 0.3);
        }

        .os-ac:hover h3 {
            color: var(--color-osac);
        }

        .pos-venda .icon-box {
            color: var(--color-pos);
            background: rgba(16, 185, 129, 0.06);
        }

        .pos-venda:hover {
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .pos-venda:hover h3 {
            color: var(--color-pos);
        }

        .footer {
            margin-top: 60px;
            margin-bottom: 30px;
            font-size: 0.75rem;
            color: #8c8c9e;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header-painel">
        <img src="../assets/logo_equipilates.png" alt="">
        <a class="btn-voltar" href="../index.php">Central ></a>
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
                <p>Equipamentos da linha contemporânea e pedidos normais</p>
            </a>

            <a href="tabela_acessorios.php" class="card-botao prod">
                <div class="icon-box">➕</div>
                <h3>Acessórios</h3>
                <p>Acessórios da linha contemporânea e pedidos normais</p>
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

            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Financeiro', 'Gerente', 'CEO', 'Desenvolvedor', 'PCP'])): ?>
                <a href="tabela_financeiro.php" class="card-botao pos-venda">
                    <div class="icon-box">💰</div>
                    <h3>Financeiro</h3>
                    <p>Fila para o controle do financeiro</p>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Pos-venda', 'Gerente', 'CEO', 'Desenvolvedor', 'PCP'])): ?>
                <a href="tabela_posVenda.php" class="card-botao pos-venda">
                    <div class="icon-box">✅</div>
                    <h3>Pós-Venda</h3>
                    <p>Fila de pedidos produzidos</p>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Expedicao', 'Gerente', 'CEO', 'Desenvolvedor', 'PCP'])): ?>
                <a href="tabela_expedição.php" class="card-botao pos-venda">
                    <div class="icon-box">✈️</div>
                    <h3>Expedição</h3>
                    <p>Fila de organização de pedidos para expedição</p>
                </a>
            <?php endif; ?>

            <a href="tabela_controle.php" class="card-botao pos-venda">
                <div class="icon-box">🎮</div>
                <h3>Controle</h3>
                <p>Fila de controle de pedidos</p>
            </a>

            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Desenvolvedor', 'gerente', 'CEO', 'PCP', 'Expedicao'])): ?>
                <a href="tabela_expedidos.php" class="card-botao pos-venda">
                    <div class="icon-box">🛬</div>
                    <h3>Pedidos expedidos</h3>
                    <p>Fila de controle de pedidos expedidos</p>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Desenvolvedor', 'gerente', 'CEO', 'PCP'])): ?>
                <a href="../Function/imprimir_etiquetas.php" class="card-botao pos-venda">
                    <div class="icon-box">🖨️</div>
                    <h3>Impressão</h3>
                    <p>Etiquetas organizadas</p>
                </a>
            <?php endif; ?>
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