<?php
require_once '../Function/trava.php';
date_default_timezone_set('America/Sao_Paulo');
class Sistema
{
    private $conn;

    //instancia o banco de dados e a conexão para usar no model
    public function __construct($db)
    {
        $this->conn = $db;
    }

    //função de mostrar a tabela com todos os seus dados
    public function mostrarTabela()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
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
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaAcessorios()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
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
                  ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function mostrarTabelaOs()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
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
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaAcessoriosOs()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
                         `Wall Unit`, 
                         `Caixa Mini`, 
                         `Caixa do Reformer`, 
                         `P. de Molas - B R I N D E`, 
                         `P. de Molas - C O M P L E T A`, 
                         `P. de Molas - P u s h T h r u`, 
                         `Caixa da Cadeira`, 
                         `Prancha de Alongamento`
                  FROM tabela_adaptada 
                  WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' 
                    AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                    AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`Caixa do Reformer`), '') IS NOT NULL AND TRIM(`Caixa do Reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da Cadeira`), '') IS NOT NULL AND TRIM(`Caixa da Cadeira`) != '0') OR
                        (NULLIF(TRIM(`Prancha de Alongamento`), '') IS NOT NULL AND TRIM(`Prancha de Alongamento`) != '0')
                    )
                  ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaFinanceiro()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Financeiro'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaPosVenda()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Pós-venda'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaExpedicao()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Expedicao'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassico()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
                         `REF. CLASSICO ALUMINIO`, 
                         `REF. CLASSICO TORRE`, 
                         `CAD. CLASSICO ALUMINIO`, 
                         `REF. CLASSICO TAUARI`, 
                         `CAD. CLASSICO TAUARI`,
                         'REFORMER HIBRIDO',
                         'WUNDA CHAIR',
                         'ELECTRIC CHAIR',
                         'ARM CHAIR',
                         'LADDER BARREL CLÁSS.',
                         'PEDI O POLE',
                         'WALL UNIT CLÁSSICO',
                         'MAT CLÁSSICO',
                         'MAT PORTÁTIL',
                         'BENCH MAT',
                         'GUILHOTINA',
                         'CARRINHO',
                         'GAIOLA'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`REF. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`REF. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TORRE`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TORRE`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`REFORMER HIBRIDO`), '') IS NOT NULL AND TRIM(`REFORMER HIBRIDO`) != '0') OR
                        (NULLIF(TRIM(`WUNDA CHAIR`), '') IS NOT NULL AND TRIM(`WUNDA CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ELECTRIC CHAIR`), '') IS NOT NULL AND TRIM(`ELECTRIC CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ARM CHAIR`), '') IS NOT NULL AND TRIM(`ARM CHAIR`) != '0') OR
                        (NULLIF(TRIM(`LADDER BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`LADDER BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`PEDI O POLE`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`WALL UNIT CLÁSSICO`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT CLÁSSICO`), '') IS NOT NULL AND TRIM(`MAT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT PORTÁTIL`), '') IS NOT NULL AND TRIM(`MAT PORTÁTIL`) != '0') OR
                        (NULLIF(TRIM(`BENCH MAT`), '') IS NOT NULL AND TRIM(`BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`GUILHOTINA`), '') IS NOT NULL AND TRIM(`GUILHOTINA`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0')
                    )
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassicoAcessorios()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
                         `CAIXA DO REFORMER CLÁSSICA`,
                         `SPINE CORRECTOR`, 
                         `SMALL BARREL`, 
                         `SUPORTE SPINE CORRECTOR`, 
                         `MINI EXTENSÃO MOVE FLOW`, 
                         `PLATAFORMA BARREL CLÁSSICO`,
                         'BARRA PUSH TRUE (BALANÇO CLASSICO)',
                         'SPACER BOX',
                         '2 x 4 (TWO BY FOUR)',
                         'KUNA BOARD',
                         'TRAVESSEIRO BENCH MAT',
                         'TRAVESSEIRO RÉGUA',
                         'TRAVESSEIRO 1/2 LUA',
                         'TRAV. CILINDRICO',
                         'TRAV. OMBREIRA (PAR)',
                         'TRAV. CABEC. 30 mm',
                         'TRAV. CABEC. 40 mm',
                         'CAPA PROT. BARREL CLÁSS.',
                         'SHEEPSKIN COVER',
                         'BASTÃO ALUMÍNIO 1,5 M',
                         'PUXADOR DE ALUMINIO',
                         'ANEL DE PILATES ARCHIVE AÇO',
                         'MAGIC SQUARE',
                         'FOOT CORREC. ALUM.',
                         'BEAN BAG',
                         'BREATH A CIZER',
                         'NECK STRETCHER',
                         'HAND TENS O METER',
                         'TOE EXERCISER',
                         'AIR PLANE BOARD',
                         'FINGER EXERCISE',
                         'PUSH UP DEVICE (PAR)',
                         'MINI BARREL',
                         'MINI SPINE'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`CAIXA DO REFORMER CLÁSSICA`), '') IS NOT NULL AND TRIM(`CAIXA DO REFORMER CLÁSSICA`) != '0') OR
                        (NULLIF(TRIM(`SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`SMALL BARREL`), '') IS NOT NULL AND TRIM(`SMALL BARREL`) != '0') OR
                        (NULLIF(TRIM(`SUPORTE SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SUPORTE SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`MINI EXTENSÃO MOVE FLOW`), '') IS NOT NULL AND TRIM(`MINI EXTENSÃO MOVE FLOW`) != '0') OR
                        (NULLIF(TRIM(`PLATAFORMA BARREL CLÁSSICO`), '') IS NOT NULL AND TRIM(`PLATAFORMA BARREL CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`), '') IS NOT NULL AND TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`) != '0') OR
                        (NULLIF(TRIM(`SPACER BOX`), '') IS NOT NULL AND TRIM(`SPACER BOX`) != '0') OR
                        (NULLIF(TRIM(`2 x 4 (TWO BY FOUR)`), '') IS NOT NULL AND TRIM(`2 x 4 (TWO BY FOUR)`) != '0') OR
                        (NULLIF(TRIM(`KUNA BOARD`), '') IS NOT NULL AND TRIM(`KUNA BOARD`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO BENCH MAT`), '') IS NOT NULL AND TRIM(`TRAVESSEIRO BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO RÉGUA`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO 1/2 LUA`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CILINDRICO`), '') IS NOT NULL AND TRIM(`TRAV. CILINDRICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. OMBREIRA (PAR)`), '') IS NOT NULL AND TRIM(`TRAV. OMBREIRA (PAR)`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 30 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 30 mm`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 40 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 40 mm`) != '0') OR
                        (NULLIF(TRIM(`CAPA PROT. BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`CAPA PROT. BARREL CLÁSS.`) != '0') OR 
                        (NULLIF(TRIM(`SHEEPSKIN COVER`), '') IS NOT NULL AND TRIM(`SHEEPSKIN COVER`) != '0') OR
                        (NULLIF(TRIM(`BASTÃO ALUMÍNIO 1,5 M`), '') IS NOT NULL AND TRIM(`BASTÃO ALUMÍNIO 1,5 M`) != '0') OR
                        (NULLIF(TRIM(`PUXADOR DE ALUMINIO`), '') IS NOT NULL AND TRIM(`PUXADOR DE ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`ANEL DE PILATES ARCHIVE AÇO`), '') IS NOT NULL AND TRIM(`ANEL DE PILATES ARCHIVE AÇO`) != '0') OR
                        (NULLIF(TRIM(`MAGIC SQUARE`), '') IS NOT NULL AND TRIM(`MAGIC SQUARE`) != '0') OR
                        (NULLIF(TRIM(`FOOT CORREC. ALUM.`), '') IS NOT NULL AND TRIM(`FOOT CORREC. ALUM.`) != '0') OR
                        (NULLIF(TRIM(`BEAN BAG`), '') IS NOT NULL AND TRIM(`BEAN BAG`) != '0') OR
                        (NULLIF(TRIM(`BREATH A CIZER`), '') IS NOT NULL AND TRIM(`BREATH A CIZER`) != '0') OR
                        (NULLIF(TRIM(`NECK STRETCHER`), '') IS NOT NULL AND TRIM(`NECK STRETCHER`) != '0') OR
                        (NULLIF(TRIM(`HAND TENS O METER`), '') IS NOT NULL AND TRIM(`HAND TENS O METER`) != '0') OR
                        (NULLIF(TRIM(`TOE EXERCISER`), '') IS NOT NULL AND TRIM(`TOE EXERCISER`) != '0') OR
                        (NULLIF(TRIM(`AIR PLANE BOARD`), '') IS NOT NULL AND TRIM(`AIR PLANE BOARD`) != '0') OR
                        (NULLIF(TRIM(`PUSH UP DEVICE (PAR)`), '') IS NOT NULL AND TRIM(`PUSH UP DEVICE (PAR)`) != '0') OR
                        (NULLIF(TRIM(`MINI BARREL`), '') IS NOT NULL AND TRIM(`MINI BARREL`) != '0') OR
                        (NULLIF(TRIM(`MINI SPINE`), '') IS NOT NULL AND TRIM(`MINI SPINE`) != '0') 
                    )
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassicoOs()
    {
        $query = "SELECT `NUMERO PEDIDO` as numero, 
                         `PRAZO DE PRODUCAO` as prazo_producao, 
                         `REF. CLASSICO ALUMINIO`, 
                         `REF. CLASSICO TORRE`, 
                         `CAD. CLASSICO ALUMINIO`, 
                         `REF. CLASSICO TAUARI`, 
                         `CAD. CLASSICO TAUARI`,
                         'REFORMER HIBRIDO',
                         'WUNDA CHAIR',
                         'ELECTRIC CHAIR',
                         'ARM CHAIR',
                         'LADDER BARREL CLÁSS.',
                         'PEDI O POLE',
                         'WALL UNIT CLÁSSICO',
                         'MAT CLÁSSICO',
                         'MAT PORTÁTIL',
                         'BENCH MAT',
                         'GUILHOTINA',
                         'CARRINHO',
                         'GAIOLA'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`REF. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`REF. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TORRE`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TORRE`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`REFORMER HIBRIDO`), '') IS NOT NULL AND TRIM(`REFORMER HIBRIDO`) != '0') OR
                        (NULLIF(TRIM(`WUNDA CHAIR`), '') IS NOT NULL AND TRIM(`WUNDA CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ELECTRIC CHAIR`), '') IS NOT NULL AND TRIM(`ELECTRIC CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ARM CHAIR`), '') IS NOT NULL AND TRIM(`ARM CHAIR`) != '0') OR
                        (NULLIF(TRIM(`LADDER BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`LADDER BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`PEDI O POLE`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`WALL UNIT CLÁSSICO`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT CLÁSSICO`), '') IS NOT NULL AND TRIM(`MAT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT PORTÁTIL`), '') IS NOT NULL AND TRIM(`MAT PORTÁTIL`) != '0') OR
                        (NULLIF(TRIM(`BENCH MAT`), '') IS NOT NULL AND TRIM(`BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`GUILHOTINA`), '') IS NOT NULL AND TRIM(`GUILHOTINA`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0')
                    )
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaControle()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, status_posvenda
                  FROM pedidos_prontos
                  WHERE status_posvenda NOT LIKE 'Finalizado'
                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaExpedidos()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, status_posvenda, data_conclusao
                  FROM pedidos_expedidos
                  WHERE status_posvenda LIKE 'Finalizado' 
                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaProducao(): array
    {
        $query = "SELECT id, numero_pedido, prazo_producao, status AS status_item, 'NORMAL' AS origem
                    FROM itens_producao 
                    WHERE equipamento NOT LIKE '%Emb.%'
                    
                    UNION ALL
                    
                    SELECT id, numero_pedido, prazo_producao, status AS status_item, 'OS' AS origem
                    FROM itens_os
                    WHERE equipamento NOT LIKE '%Emb.%'
                    
                    ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $todosItens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pedidosAgrupados = [];
            $itensProcessadosPorPedido = [];

            foreach ($todosItens as $item) {
                $numPedido = $item['numero_pedido'];
                $idItem = $item['id'];
                $origem = $item['origem'];

                if (empty($numPedido)) {
                    continue;
                }

                if (!isset($itensProcessadosPorPedido[$numPedido])) {
                    $itensProcessadosPorPedido[$numPedido] = [];
                }

                if (in_array($idItem, $itensProcessadosPorPedido[$numPedido])) {
                    continue;
                }

                $itensProcessadosPorPedido[$numPedido][] = $idItem;

                if (!isset($pedidosAgrupados[$numPedido])) {
                    $pedidosAgrupados[$numPedido] = [
                        'numero_pedido'   => $numPedido,
                        'origem'          => $origem,
                        'prazo_producao'  => $item['prazo_producao'],
                        'total_itens'     => 0,
                        'itens_concluidos' => 0
                    ];
                }

                $pedidosAgrupados[$numPedido]['total_itens']++;

                if ($item['status_item'] === 'Embalado' || $item['status_item'] === 'E') {
                    $pedidosAgrupados[$numPedido]['itens_concluidos']++;
                }
            }

            return array_values($pedidosAgrupados);
        } catch (PDOException $e) {
            echo "Erro ao buscar fila de produção: " . $e->getMessage();
            return [];
        }
    }
}
