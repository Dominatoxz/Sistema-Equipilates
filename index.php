<?php
require_once './Controller/SistemaController.php';

$sistemaController = new SistemaController();

if (isset($_GET['pagina']) && $_GET['pagina'] === 'os') {
    $sistemaController->os();
} elseif (isset($_GET['pagina']) && $_GET['pagina'] === 'posVenda') {
    $sistemaController->posVenda();
} elseif (isset($_GET['pagina']) && $_GET['pagina'] === 'acessorios') {
    $sistemaController->acessorios();
} else {
    $sistemaController->index();
}
