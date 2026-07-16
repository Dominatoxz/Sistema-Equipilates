<?php
Class Database {
    public $conn;
    private $db_host = 'localhost';
    private $db_name = 'u109029190_db_GN6XeAZr'; //u109029190_db_GN6XeAZr
    private $db_user = 'u109029190_usr_GN6XeAZr'; //u109029190_usr_GN6XeAZr
    private $db_password = 'Equipilates2026@#'; //Equipilates2026@#

    public function getConnection() {
        
        $this->conn = null;
        try{
            //conexão com o banco de dados usando PDO
            $this->conn = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
            $this->conn->exec("SET time_zone = '-03:00';");
            //configura o PDO para lançar exceções em caso de erro
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //pega a mensagem de erro e associa a uma variavel
        }catch(PDOException $e) {
            //getMessage pega a mensagem de erro e exibe na tela
            echo "Erro de conexão." . $e->getMessage();
        
        }
        //com o sucesso da conexão, retorna a propria conexão
        return $this->conn;
    }
}

//$env:DB_HOST="localhost"; $env:DB_NAME="planilha_db"; $env:DB_USER="root"; $env:DB_PASSWORD="equipilates26"; php -S 0.0.0.0:8000
?>

