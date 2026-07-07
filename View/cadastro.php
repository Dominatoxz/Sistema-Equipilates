<?php
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$pdo = $db; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EQUIPILATES - Cadastro de Usuários</title>
    <style>
        :root {
            --bg-main: #040408;
            --bg-gradient: radial-gradient(circle at 50% 30%, #0c0f1d 0%, #030306 100%);
            --panel-bg: rgba(10, 11, 18, 0.65);
            --border-tech: rgba(189, 0, 255, 0.1); 
            
            --neon-blue: #00f0ff;
            --neon-purple: #bd00ff;
            --neon-amber: #ff9d00;
            --neon-green: #10b981;
            --neon-red: #ff3838;
            --text-primary: #ffffff;
            --text-secondary: #7e8494;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.97) translateY(15px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg-main);
            background: var(--bg-gradient);
            color: var(--text-primary);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
            position: relative;
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

        .container-cadastro {
            position: relative;
            z-index: 1;
            background: var(--panel-bg);
            border-radius: 16px;
            border: 1px solid var(--border-tech);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
            text-align: center;
            margin-bottom: 30px;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .container-cadastro::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 15px; height: 15px;
            border-top: 2px solid var(--neon-amber);
            border-right: 2px solid var(--neon-amber);
            border-radius: 0 16px 0 0;
        }

        h1 {
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 6px;
            font-weight: 800;
            background: linear-gradient(180deg, #ffffff 30%, #828b9e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            font-size: 0.85rem;
            color: var(--neon-amber); 
            margin-top: 0;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(255, 157, 0, 0.3);
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
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
            padding: 13px 16px;
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
            border-color: rgba(255, 145, 0, 0.5);
            background: rgba(189, 0, 255, 0.02);
            box-shadow: 0 0 15px rgba(189, 0, 255, 0.15), inset 0 0 8px rgba(189, 0, 255, 0.05);
        }

        .btn-cadastrar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #10b981, #059669);
            color: #020205;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.25);
        }

        .btn-cadastrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.45);
            filter: brightness(1.1);
        }

        .btn-voltar {
            display: inline-block;
            margin-top: 25px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-voltar:hover {
            color: var(--neon-blue);
            text-shadow: 0 0 8px rgba(0, 240, 255, 0.4);
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

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-left-color: var(--neon-green);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.05);
        }

        .alert-danger {
            background: rgba(255, 56, 56, 0.08);
            color: #ff6b6b;
            border: 1px solid rgba(255, 56, 56, 0.2);
            border-left-color: var(--neon-red);
            box-shadow: 0 0 15px rgba(255, 56, 56, 0.05);
        }

        .tabela-usuarios {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            border-collapse: collapse;
            background: var(--panel-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .tabela-usuarios th, .tabela-usuarios td {
            padding: 14px 18px;
            text-align: left;
        }

        .tabela-usuarios th {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 2px solid rgba(189, 0, 255, 0.2);
            color: var(--text-primary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .tabela-usuarios td {
            color: #d1d5db;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            transition: background 0.2s ease;
        }

        .tabela-usuarios tr:last-child td {
            border-bottom: none;
        }

        .tabela-usuarios tr:hover td {
            background: rgba(0, 240, 255, 0.02);
            color: var(--neon-blue);
        }
    </style>
</head>
<body>

    <div class="container-cadastro">
        <h1>Controle de Acesso</h1>
        <h2>Novo Usuário</h2>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'sucesso'): ?>
                <div class="alert alert-success">sys_log: Usuário gravado com sucesso!</div>
            <?php elseif ($_GET['status'] === 'existe'): ?>
                <div class="alert alert-danger">err_log: ID de credencial indisponível.</div>
            <?php elseif ($_GET['status'] === 'erro'): ?>
                <div class="alert alert-danger">err_log: Falha crítica na gravação (DB).</div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="../Function/processa_cadastro.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="usuario">ID do Usuário (Login)</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ex: carlos.producao" required>
            </div>

            <div class="form-group">
                <label for="senha">Chave Operacional (Senha)</label>
                <input type="password" id="senha" name="senha" placeholder="Defina a senha de acesso" required>
            </div>

            <button type="submit" class="btn-cadastrar">Entrar</button>
        </form>

        <a href="../index.php" class="btn-voltar">Retornar à central</a>
    </div>

    <table class="tabela-usuarios">
        <thead>
            <tr>
                <th>Conexões Ativas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $usuarios = $pdo->query("SELECT usuario FROM usuarios ORDER BY usuario ASC")->fetchAll();
            foreach ($usuarios as $user) {
                echo "<tr><td>🔒 " . htmlspecialchars($user['usuario'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>