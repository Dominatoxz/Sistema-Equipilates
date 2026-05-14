<?php
class Sistema {
    private $conn;

    //instancia o banco de dados e a conexão para usar no model
    public function __construct($db){
        $this->conn = $db;

    }

    //função de mostrar a tabela com todos os seus dados
   public function mostrarTabela(){
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `Reformer Excellence`, 
                         `Reformer Torre`, 
                         `Cadilac Excelence`, 
                         `Step Chair Excelence`, 
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>