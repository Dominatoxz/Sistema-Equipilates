<?php
class Sistema {
    private $conn;
    private $table = "pedidos";

    public function __construct($db){
        $this->conn = $db;

    }

    public function mostrarTabela(){
        $query = "SELECT numero_pedido, item, quantidade
            FROM pedidos_itens";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>