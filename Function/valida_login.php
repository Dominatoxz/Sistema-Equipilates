<?php
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
session_start();
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$pdo = $db; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['senha'];

    if (!empty($usuario) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT id, usuario, senha_hash FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);
        $dados_usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados_usuario && password_verify($senha, $dados_usuario['senha_hash'])) {
            
            session_regenerate_id(true);
            
            $_SESSION['usuario_id'] = $dados_usuario['id'];
            $_SESSION['usuario_logado'] = $dados_usuario['usuario'];
            $_SESSION['criado_em'] = time();

            header("Location: ../index.php");
            exit();
        } else {
            header("Location: ../login.php?erro=dados_invalidos");
            exit();
        }
    }
}