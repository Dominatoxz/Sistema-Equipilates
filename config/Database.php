<?php
Class Database {
    //variáveis do banco de dados e a variavel que representa a conexão
    private $host = "localhost";
    private $db_name = "planilha_db";
    private $user = "root";
    private $password = "equipilates26";
    public $conn;

    //função para fazer a conexão com o banco de dados (fazer amanhã o link da planilha com o banco de dados)
    public function getConnetion() {
        $this->conn = null;

        try{
            //conexão com o banco de dados usando PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->user, $this->password); 
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
?>