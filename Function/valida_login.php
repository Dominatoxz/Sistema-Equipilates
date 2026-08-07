<?php
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once '../config/Database.php';
require_once __DIR__ . '/csrf.php';

const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_BLOQUEIO_MINUTOS = 15;

$database = new Database();
$db = $database->getConnection();

$pdo = $db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? null)) {
        header("Location: ../login.php?erro=dados_invalidos");
        exit();
    }

    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['senha'];

    if (empty($usuario) || empty($senha)) {
        header("Location: ../login.php?erro=dados_invalidos");
        exit();
    }

    $stmtBloqueio = $pdo->prepare("SELECT bloqueado_ate FROM login_tentativas WHERE usuario = :usuario LIMIT 1");
    $stmtBloqueio->execute(['usuario' => $usuario]);
    $tentativa = $stmtBloqueio->fetch(PDO::FETCH_ASSOC);

    if ($tentativa && $tentativa['bloqueado_ate'] !== null && strtotime($tentativa['bloqueado_ate']) > time()) {
        header("Location: ../login.php?erro=bloqueado");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, usuario, senha_hash, nivel_acesso FROM usuarios WHERE usuario = :usuario LIMIT 1");
    $stmt->execute(['usuario' => $usuario]);
    $dados_usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados_usuario && password_verify($senha, $dados_usuario['senha_hash'])) {
        // Login certo: zera qualquer histórico de tentativas erradas.
        $stmtReset = $pdo->prepare("DELETE FROM login_tentativas WHERE usuario = :usuario");
        $stmtReset->execute(['usuario' => $usuario]);

        session_regenerate_id(true);

        $_SESSION['usuario_id']     = $dados_usuario['id'];
        $_SESSION['usuario_logado'] = $dados_usuario['usuario'];
        $_SESSION['nivel_acesso']   = $dados_usuario['nivel_acesso'];
        $_SESSION['criado_em']      = time();

        header("Location: ../index.php");
        exit();
    }

    // Login errado: registra a tentativa e bloqueia temporariamente se
    // passar do limite.
    $stmtUpsert = $pdo->prepare("INSERT INTO login_tentativas (usuario, tentativas, ultima_tentativa)
                                  VALUES (:usuario, 1, NOW())
                                  ON DUPLICATE KEY UPDATE tentativas = tentativas + 1, ultima_tentativa = NOW()");
    $stmtUpsert->execute(['usuario' => $usuario]);

    $stmtContagem = $pdo->prepare("SELECT tentativas FROM login_tentativas WHERE usuario = :usuario LIMIT 1");
    $stmtContagem->execute(['usuario' => $usuario]);
    $totalTentativas = (int) $stmtContagem->fetchColumn();

    if ($totalTentativas >= LOGIN_MAX_TENTATIVAS) {
        $stmtBloquear = $pdo->prepare("UPDATE login_tentativas
                                        SET bloqueado_ate = DATE_ADD(NOW(), INTERVAL :minutos MINUTE), tentativas = 0
                                        WHERE usuario = :usuario");
        $stmtBloquear->execute([
            'minutos' => LOGIN_BLOQUEIO_MINUTOS,
            'usuario' => $usuario,
        ]);

        header("Location: ../login.php?erro=bloqueado");
        exit();
    }

    header("Location: ../login.php?erro=dados_invalidos");
    exit();
}
