<?php
require_once './Controller/SistemaController.php';

$sistemaController = new SistemaController();

if (isset($_GET['pagina']) && $_GET['pagina'] === 'os') {
    $sistemaController->os();
} else {
    $sistemaController->index();
}

?>