<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Function/csrf.php';
$csrfToken = gerarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQUIPILATES - Login</title>
    <style>
        :root {
            --bg-main: #040408;
            --bg-gradient: radial-gradient(circle at 50% 30%, #0c0f1d 0%, #030306 100%);
            --panel-bg: rgba(10, 11, 18, 0.65);
            --border-tech: rgba(0, 240, 255, 0.08);

            --neon-blue: #00f0ff;
            --neon-amber: #ff9d00;
            --neon-red: #ff3838;
            --text-primary: #ffffff;
            --text-secondary: #7e8494;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(15px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-main);
            background: var(--bg-gradient);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.01) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.01) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: var(--panel-bg);
            border-radius: 16px;
            border: 1px solid var(--border-tech);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 40px rgba(0, 240, 255, 0.03);
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-sizing: border-box;
            text-align: center;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 15px;
            height: 15px;
            border-top: 2px solid var(--neon-blue);
            border-right: 2px solid var(--neon-blue);
            border-radius: 0 16px 0 0;
        }

        .login-container img {
            max-height: 120px;
            width: 180px;
            filter: drop-shadow(0 0 12px rgba(0, 240, 255, 0.25)) brightness(1.1);
        }

        h1 {
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 6px;
            font-weight: 800;
            background: linear-gradient(180deg, #ffffff 30%, #828b9e) 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            font-size: 0.85rem;
            color: var(--neon-amber);
            margin-top: 0;
            margin-bottom: 35px;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 600;
            text-shadow: 0 0 8px rgba(255, 157, 0, 0.3);
        }

        .form-group {
            text-align: left;
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 700;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            font-size: 0.95rem;
            color: #ffffff;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: #4a4f5c;
        }

        .form-group input:focus {
            outline: none;
            border-color: rgba(0, 240, 255, 0.5);
            background: rgba(0, 240, 255, 0.02);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.15), inset 0 0 8px rgba(0, 240, 255, 0.05);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00b0ff, #00f0ff);
            color: #020205;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            margin-top: 15px;
            box-shadow: 0 4px 20px rgba(0, 240, 255, 0.25);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 240, 255, 0.45);
            filter: brightness(1.1);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: left;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-left: 4px solid transparent;
        }

        .alert-danger {
            background: rgba(255, 56, 56, 0.08);
            color: #ff6b6b;
            border: 1px solid rgba(255, 56, 56, 0.2);
            border-left-color: var(--neon-red);
            box-shadow: 0 0 15px rgba(255, 56, 56, 0.05);
        }

        .alert-warning {
            background: rgba(255, 157, 0, 0.08);
            color: #ffb13b;
            border: 1px solid rgba(255, 157, 0, 0.2);
            border-left-color: var(--neon-amber);
            box-shadow: 0 0 15px rgba(255, 157, 0, 0.05);
        }

        .footer-login {
            margin-top: 40px;
            font-size: 0.7rem;
            color: var(--text-secondary);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
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
                <div class="alert alert-danger">⚠️ LOG: Usuário ou senha incorretos.</div>
            <?php elseif ($_GET['erro'] === 'restrito'): ?>
                <div class="alert alert-warning">🔒 AVISO: Autenticação necessária para acesso.</div>
            <?php elseif ($_GET['erro'] === 'bloqueado'): ?>
                <div class="alert alert-danger">⏱️ Muitas tentativas erradas. Aguarde alguns minutos antes de tentar de novo.</div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="Function/valida_login.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="usuario">ID Usuário</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ex: joao.producao" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Chave de Acesso</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="footer-login">
            EQUIPILATES &copy; <?= date('Y'); ?> // SECURITY CORE
        </div>
    </div>

</body>

</html>