<?php
require_once './Model/Sistema.php';
require_once './config/Database.php';

class SistemaController {
    private $sistema;

    public function __construct() {
    //Preparar conexão com o BD
        $database = new Database();
        $db = $database->getConnection();
        //instanciar a Model Sistema
        $this->sistema = new Sistema($db);
    }

    //listar todos os itens na tela inicial
    public function index() {
        //pede lista de dados ao Model
        $pedidos = $this->sistema->mostrarTabela();
        require_once './View/tabela.php';
    }

    public function os() {
        //pede lista de dados ao Model
        $pedidos = $this->sistema->mostrarTabelaOs();
        require_once './View/tabela_os.php';
    }

    public function acessorios() {
        $pedidos = $this->sistema->mostrarTabelaAcessorios();
        require_once './View/tabela_acessorios.php';
    }

    public function posVenda() {
        //pede lista de dados ao Model
        $pedidos = $this->sistema->mostrarFilaPosVenda();
        require_once './View/tabela_posVenda.php';
    }
}
?>