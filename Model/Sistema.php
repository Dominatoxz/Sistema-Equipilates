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
                         `PRAZO DE PRODUÇÃO` as prazo_producao, 
                         `Reformer Excellence`, 
                         `Reformer Torre`, 
                         `Cadilac Excelence`, 
                         `Step Chair Excelence`, 
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%';
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaOs(){
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUÇÃO` as prazo_producao, 
                         `Reformer Excellence`, 
                         `Reformer Torre`, 
                         `Cadilac Excelence`, 
                         `Step Chair Excelence`, 
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%';
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>