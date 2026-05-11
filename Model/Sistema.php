<?php
class Sistema {
    private $conn;
    private $table = "pedidos";

    //instancia o banco de dados e a conexão para usar no model
    public function __construct($db){
        $this->conn = $db;

    }

    //função de mostrar a tabela com todos os seus dados
    public function mostrarTabela(){
        $query = "SELECT numero_pedido, item, quantidade
            FROM pedidos_itens";
        //prepara a query SQL para execução do comando
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        //fetchAll retorna um array associativo contendo todas as linhas da consulta 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>