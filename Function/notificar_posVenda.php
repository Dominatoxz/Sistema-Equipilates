<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
require_once '../Function/notificar_pos_producao.php';

header('Content-Type: application/json');
$database = new Database();
$db = $database->getConnection();
echo json_encode(notificarPosProducao($db, $_GET['pedido'] ?? null));
