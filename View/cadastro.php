<?php
require_once '../Function/trava.php';
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container-cadastro {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
            text-align: center;
            margin-bottom: 30px;
        }

        h1 { font-size: 1.8rem; color: #212529; margin-bottom: 5px; text-transform: uppercase; }
        h2 { font-size: 1rem; color: #e67e22; margin-top: 0; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }

        .form-group { text-align: left; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.9rem; margin-bottom: 5px; color: #495057; font-weight: 600; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 1rem; box-sizing: border-box;
        }
        .form-group input:focus { outline: none; border-color: #e67e22; box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.15); }

        .btn-cadastrar {
            width: 100%; padding: 12px; background-color: #2ecc71; color: white; border: none; border-radius: 6px;
            font-size: 1rem; font-weight: bold; cursor: pointer; text-transform: uppercase; transition: background 0.2s;
        }
        .btn-cadastrar:hover { background-color: #27ae60; }

        .btn-voltar {
            display: inline-block; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-size: 0.9rem;
        }
        .btn-voltar:hover { color: #34495e; text-decoration: underline; }

        .alert { padding: 10px; border-radius: 6px; font-size: 0.9rem; margin-bottom: 20px; text-align: left; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .tabela-usuarios {
            width: 100%; max-width: 500px; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef;
        }
        .tabela-usuarios th, .tabela-usuarios td { padding: 12px; text-align: left; border-bottom: 1px solid #e9ecef; }
        .tabela-usuarios th { background-color: #f1f3f5; color: #495057; font-size: 0.9rem; text-transform: uppercase; }
        .tabela-usuarios td { color: #6c757d; font-size: 0.95rem; }
    </style>
</head>
<body>

    <div class="container-cadastro">
        <h1>Controle de Acesso</h1>
        <h2>Novo Usuário</h2>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'sucesso'): ?>
                <div class="alert alert-success">✅ Usuário cadastrado com sucesso!</div>
            <?php elseif ($_GET['status'] === 'existe'): ?>
                <div class="alert alert-danger">❌ Este nome de usuário já está em uso.</div>
            <?php elseif ($_GET['status'] === 'erro'): ?>
                <div class="alert alert-danger">❌ Erro ao salvar no banco de dados.</div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="../Function/processa_cadastro.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="usuario">Nome de Usuário (Login)</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ex: carlos.producao" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha Operacional</label>
                <input type="password" id="senha" name="senha" placeholder="Digite a senha" required>
            </div>

            <button type="submit" class="btn-cadastrar">Salvar Usuário</button>
        </form>

        <a href="../index.php" class="btn-voltar">< Voltar para a Central</a>
    </div>

    <table class="tabela-usuarios">
        <thead>
            <tr>
                <th>Usuários Ativos</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $usuarios = $pdo->query("SELECT usuario FROM usuarios ORDER BY usuario ASC")->fetchAll();
            foreach ($usuarios as $user) {
                echo "<tr><td>👤 " . htmlspecialchars($user['usuario'], ENT_QUOTES, 'UTF-8') . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>