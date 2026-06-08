<?php
require_once './Function/trava.php'; 
require_once './global.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Central de Linhas</title>
<style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
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

        h1 {
            font-size: 2.3rem;
            color: #212529;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            font-weight: 700;
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
            grid-template-columns: repeat(auto-fit, minmax(280px, 3, 1fr));
            gap: 25px;
            padding: 10px;
            width: 100%;
            max-width: 700px; 
            margin: 0 auto;
        }

        .card-botao {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            padding: 40px 20px;
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
            margin: 15px 0 0 0;
            font-size: 1.4rem;
            color: #212529;
            transition: color 0.3s ease;
            font-weight: 600;
        }

        .icon-box {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
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

        .linha-contemporanea .icon-box { background-color: #eaf2f8; color: #3498db; }
        .linha-contemporanea:hover { border-color: #3498db; }
        .linha-contemporanea:hover h3 { color: #3498db; }

        .linha-classica .icon-box { background-color: #fdf2e9; color: #e67e22; }
        .linha-classica:hover { border-color: #e67e22; }
        .linha-classica:hover h3 { color: #e67e22; }


        .footer {
            margin-top: 50px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #adb5bd;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="header-painel">
        <img src="assets/logo_equipilates.png" alt="">
    </div>
    <div class="container">
        <h1>Linhas</h1>
        <p class="subtitle">"Olhos e Mente na Tarefa."</p>
        <p class="sub-subtitle">- Luiz Kelly</p>

        <div class="grid-painel">
            <a href="View/central_contemporaneo.php" class="card-botao linha-contemporanea">
                <div class="icon-box">🧬</div>
                <h3>Linha Contemporânea</h3>
            </a>

            <a href="View/central_classico.php" class="card-botao linha-classica">
                <div class="icon-box">🏛️</div>
                <h3>Linha Clássica</h3>
            </a>
            <a href="View/cadastro.php" class="card-botao linha-classica">
                <div class="icon-box">📋</div>
                <h3>Cadastrar Usuário</h3>
            </a>
        </div>
    </div>

    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>

    <script>
        document.querySelectorAll('.card-botao').forEach(link => {
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