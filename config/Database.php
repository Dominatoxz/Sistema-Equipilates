<?php
Class Database {
    public $conn;

    //função para fazer a conexão com o banco de dados (fazer amanhã o link da planilha com o banco de dados)
    public function getConnection() {
        $this->conn = null;
        try{
            //conexão com o banco de dados usando PDO
            $this->conn = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASSWORD'));
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