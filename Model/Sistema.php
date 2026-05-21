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
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUÇÃO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      
    public function mostrarTabelaAcessorios(){
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUÇÃO` as prazo_producao, 
                         `Wall Unit`, 
                         `Caixa Mini`, 
                         `caixa do reformer`, 
                         `P. de Molas - B R I N D E`, 
                         `P. de Molas - C O M P L E T A`, 
                         `P. de Molas - P u s h T h r u`, 
                         `Caixa da cadeira`, 
                         `prancha de alongamento`
                  FROM tabela_adaptada 
                  WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' 
                    AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                    AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`caixa do reformer`), '') IS NOT NULL AND TRIM(`caixa do reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da cadeira`), '') IS NOT NULL AND TRIM(`Caixa da cadeira`) != '0') OR
                        (NULLIF(TRIM(`prancha de alongamento`), '') IS NOT NULL AND TRIM(`prancha de alongamento`) != '0')
                    )
                  ORDER BY STR_TO_DATE(`PRAZO DE PRODUÇÃO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
                  
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
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUÇÃO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function mostrarFilaPosVenda(){
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Pendente'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function darBaixaPosVenda($id){
        $query = "UPDATE pedidos_prontos SET status_posvenda = 'Concluído' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

}
?>