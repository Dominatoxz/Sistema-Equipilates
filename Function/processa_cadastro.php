<?php
require_once 'trava.php';
require_once '../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$pdo = $db;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['senha'];

    if (!empty($usuario) && !empty($senha)) {

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha_hash) VALUES (:usuario, :senha)");
            $stmt->execute([
                'usuario' => $usuario,
                'senha'   => $senha_hash
            ]);

            header("Location: ../View/cadastro.php?status=sucesso");
            exit();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                header("Location: ../View/cadastro.php?status=existe");
            } else {
                header("Location: ../View/cadastro.php?status=erro");
            }
            exit();
        }
    }
}
