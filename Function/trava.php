<?php
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
?>