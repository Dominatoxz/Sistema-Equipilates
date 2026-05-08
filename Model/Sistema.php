<?php
class Sistema {
    private $conn;
    private $table = "pedidos";

    public function __construct($db){
        $this->conn = $db;

    }

    public function mostrarTabela(){
        $query = "SELECT p.id_pedido, p.num_pedido, c.codigo_cor, p.prazo_producao
            FROM pedidos p
            JOIN pedido_produtos pp ON p.id_pedido = pp.id_pedido
            JOIN produtos pr ON pp.id_produto = pr.id_produto
            JOIN cores c ON p.id_cor = c.id_cor";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>