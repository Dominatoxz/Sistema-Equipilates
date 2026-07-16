<?php
require_once './Function/trava.php'; 
date_default_timezone_set('America/Sao_Paulo');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Central de Linhas</title>
    <style>
        :root {
            --bg-main: #040408;
            --bg-gradient: radial-gradient(circle at 50% 30%, #0c0f1d 0%, #030306 100%);
            --panel-bg: rgba(10, 11, 18, 0.6);
            --border-tech: rgba(0, 240, 255, 0.05);
            
            --neon-blue: #00f0ff;
            --neon-amber: #ff9d00;
            --neon-purple: #bd00ff;
            --neon-red: #ff3b30; 
            --text-primary: #ffffff;
            --text-secondary: #7e8494;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-main);
            background: var(--bg-gradient);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: opacity 0.3s ease-in-out;
            opacity: 1;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        body.fade-out {
            opacity: 0 !important;
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 1000px;
            width: 100%;
            padding: 40px 20px;
            box-sizing: border-box;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .header-painel { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin-bottom: 40px; 
            width: 100%;
            max-width: 850px;
            position: relative; 
        }

        .header-painel img {
            max-height: 160px;
            width: 200px;
            filter: drop-shadow(0 0 12px rgba(0, 240, 255, 0.2)) brightness(1.1);
        }

    @keyframes glowSair {
        0%, 100% { 
            box-shadow: 0 0 10px rgba(255, 59, 48, 0.3); 
            border-color: rgba(255, 59, 48, 0.3);
        }
        50% { 
            box-shadow: 0 0 20px rgba(255, 59, 48, 0.6); 
            border-color: rgba(255, 59, 48, 0.7);
        }
    }

    .header-painel a.btn-sair {
        position: absolute;
        right: 10px;
        color: #ff3b30; 
        text-decoration: none;
        font-weight: 700; 
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        border: 1px solid rgba(255, 59, 48, 0.3);
        background: rgba(255, 59, 48, 0.05); 
        padding: 10px 20px; 
        border-radius: 6px;
        backdrop-filter: blur(8px);
        animation: glowSair 2s infinite ease-in-out;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .header-painel a.btn-sair:hover {
        color: #ffffff;
        background: #ff3b30; 
        box-shadow: 0 0 25px #ff3b30;
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
        transform: translateY(-2px) scale(1.05);
        animation: none; 
    }
        h1 {
            font-size: 2.6rem;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-bottom: 8px;
            font-weight: 900;
            background: linear-gradient(180deg, #ffffff 30%, #5d667a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.8));
        }

        p.subtitle {
            font-size: 1rem;
            color: var(--neon-amber);
            font-style: normal;
            max-width: 700px;
            margin: 0 auto 4px auto;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
            opacity: 0.8;
        }

        p.sub-subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0;
            margin-bottom: 50px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .grid-painel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            padding: 10px;
            width: 100%;
            max-width: 850px; 
            margin: 0 auto;
        }

        .card-botao {
            background: var(--panel-bg);
            border-radius: 12px;
            border: 1px solid var(--border-tech);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 45px 25px;
            text-decoration: none;
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .card-botao h3 {
            margin: 20px 0 0 0;
            font-size: 1.15rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            font-weight: 700;
            color: #e2e8f0;
        }

        .icon-box {
            width: 65px;
            height: 65px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-botao:hover {
            transform: translateY(-4px);
            background: rgba(16, 19, 35, 0.8);
        }

        .card-botao:hover .icon-box {
            transform: scale(1.1);
            background: transparent;
        }

        .linha-contemporanea .icon-box { color: var(--neon-blue); text-shadow: 0 0 10px rgba(0,240,255,0.5); }
        .linha-contemporanea:hover { border-color: rgba(0, 240, 255, 0.4); box-shadow: 0 0 30px rgba(0, 240, 255, 0.15); }
        .linha-contemporanea:hover h3 { color: var(--neon-blue); text-shadow: 0 0 8px rgba(0,240,255,0.3); }

        .linha-cadastro .icon-box { color: var(--neon-purple); text-shadow: 0 0 10px rgba(189,0,255,0.5); }
        .linha-cadastro:hover { border-color: rgba(189, 0, 255, 0.4); box-shadow: 0 0 30px rgba(189, 0, 255, 0.15); }
        .linha-cadastro:hover h3 { color: var(--neon-purple); text-shadow: 0 0 8px rgba(189,0,255,0.3); }

        .footer {
            margin-top: 60px;
            margin-bottom: 25px;
            font-size: 0.7rem;
            color: var(--text-secondary);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header-painel">
        <img src="assets/logo_equipilates.png" alt="Equipilates">
        <a class="btn-sair" href="Function/logout.php">Sair 🚪</a>
    </div>
    
    <div class="container">
        <h1>Linhas</h1>
        <p class="subtitle">"Olhos e Mente na Tarefa."</p>
        <div class="grid-painel">
            <a href="View/central_contemporaneo.php" class="card-botao linha-contemporanea">
                <div class="icon-box">🧬</div>
                <h3>Contemporânea</h3>
            </a>
            
            <?php if (isset($_SESSION['nivel_acesso']) && in_array($_SESSION['nivel_acesso'], ['Gerente', 'CEO', 'Desenvolvedor'])): ?>
            <a href="View/cadastro.php" class="card-botao linha-cadastro">
                <div class="icon-box">📋</div>
                <h3>Novo Usuário</h3>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>

    <script>
        document.querySelectorAll('.card-botao, .btn-sair').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); 
                const destino = this.getAttribute('href');
                
                document.body.classList.add('fade-out'); 
                
                setTimeout(() => {
                    window.location.href = destino; 
                }, 300); 
            });
        });
    </script>
</body>
</html>