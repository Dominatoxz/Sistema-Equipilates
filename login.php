<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQUIPILATES - Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-container {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        .login-container img {
            max-height: 60px;
            margin-bottom: 20px;
            width: auto;
        }

        h1 {
            font-size: 1.8rem;
            color: #212529;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2 {
            font-size: 1rem;
            color: #e67e22; 
            margin-top: 0;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1rem;
            background-color: #fff;
            color: #212529;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #d35400;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .footer-login {
            margin-top: 30px;
            font-size: 0.8rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="assets/logo_equipilates.png" alt="Logo Equipilates">
        
        <h1>Painel Operacional</h1>
        <h2>Controle de Acesso</h2>

        <?php if (isset($_GET['erro'])): ?>
            <?php if ($_GET['erro'] === 'dados_invalidos'): ?>
                <div class="alert alert-danger">Usuário ou senha incorretos.</div>
            <?php elseif ($_GET['erro'] === 'restrito'): ?>
                <div class="alert alert-warning">Por favor, faça login para acessar o painel.</div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="Function/valida_login.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ex: joao.producao" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-login">Entrar no Sistema</button>
        </form>

        <div class="footer-login">
            EQUIPILATES &copy; <?= date('Y'); ?>
        </div>
    </div>

</body>
</html>