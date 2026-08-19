<?php
require_once __DIR__ . '/../global.php';
class Database
{
    public $conn;
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_password;

    public function __construct()
    {
        $this->db_host = getenv('DB_HOST'); //getenv('DB_HOST')
        $this->db_name = getenv('DB_NAME'); //getenv('DB_NAME')
        $this->db_user = getenv('DB_USER'); //getenv('DB_USER')
        $this->db_password = getenv('DB_PASSWORD'); //getenv('DB_PASSWORD')
    }

    public function getConnection()
    {

        $this->conn = null;
        try {
            //conexão com o banco de dados usando PDO
            $this->conn = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
            $this->conn->exec("SET time_zone = '-03:00';");
            //configura o PDO para lançar exceções em caso de erro
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //pega a mensagem de erro e associa a uma variavel
        } catch (PDOException $e) {
            //getMessage pega a mensagem de erro e exibe na tela
            echo "Erro de conexão.";
        }
        //com o sucesso da conexão, retorna a propria conexão
        return $this->conn;
    }
}