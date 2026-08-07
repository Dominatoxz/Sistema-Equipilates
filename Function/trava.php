<?php
require_once __DIR__ . '/cargos.php';
require_once __DIR__ . '/csrf.php';

ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_logado'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?erro=restrito");
    exit();
}

if (!isset($_SESSION['criado_em'])) {
    $_SESSION['criado_em'] = time();
} else if (time() - $_SESSION['criado_em'] > 604800) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?erro=restrito");
    exit();
}

/**
 * @param array 
 */
function verificarAcessoSetor(array $cargosPermitidos)
{
    if (!isset($_SESSION['nivel_acesso']) || !in_array($_SESSION['nivel_acesso'], $cargosPermitidos)) {

        echo "
        <!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; text-align: center; padding: 50px; }
                .box-erro { background: white; max-width: 500px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                h2 { color: #e74c3c; margin-top: 0; }
                p { color: #555; }
                .btn-voltar { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='box-erro'>
                <h2>Acesso Negado 🛑</h2>
                <p>O seu nível de acesso (<strong>" . htmlspecialchars($_SESSION['nivel_acesso'] ?? 'Nenhum') . "</strong>) não tem permissão para aceder a esta página.</p>
                <a href='javascript:history.back()' class='btn-voltar'>Voltar</a>
            </div>
        </body>
        </html>";

        exit;
    }
}
