<?php
require_once '../config/Database.php';

$pedido = $_GET['pedido'] ?? null;
$tipoTela = $_GET['tipo_tela'] ?? 'producao';

if (!$pedido) {
    echo json_encode(['success' => false, 'error' => 'Pedido não informado.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $totalPendentes = 0;
    $isOS = (stripos($pedido, 'os') !== false);

    if ($isOS) {
        if ($tipoTela === 'os_equipamentos') {
            $sqlOs = "SELECT COUNT(*) FROM itens_os 
                      WHERE numero_pedido = ? 
                        AND status != 'Embalado'
                        AND equipamento NOT LIKE 'Caixa%' 
                        AND equipamento NOT LIKE 'Prancha%' 
                        AND equipamento NOT LIKE 'Molas%'
                        AND equipamento NOT LIKE 'Acessorio%'";
        } else if ($tipoTela === 'os_acessorios') {
            $sqlOs = "SELECT COUNT(*) FROM itens_os 
                      WHERE numero_pedido = ? 
                        AND status != 'Embalado'
                        AND (equipamento LIKE 'Caixa%' 
                          OR equipamento LIKE 'Prancha%' 
                          OR equipamento LIKE 'Molas%'
                          OR equipamento LIKE 'Acessorio%')";
        } else {
            $sqlOs = 'SELECT COUNT(*) FROM itens_os WHERE numero_pedido = ? AND status != "Embalado"';
        }
    } else {
        $equipamentosPrincipais = [
            'Reformer Excellence',
            'Reformer Torre',
            'Cadilac Excelence',
            'Step Chair Excelence',
            'Lader Barrel Excelence',
            'Wall Unit',
            'Carrinho Excellence',
            'Carrinho Torre',
            'Gaiola Cadilac'
        ];

        $placeholders = implode(',', array_fill(0, count($equipamentosPrincipais), '?'));

        $sqlProd = "SELECT COUNT(*) FROM itens_producao 
                    WHERE numero_pedido = ? 
                    AND equipamento IN ($placeholders) 
                    AND status != 'Embalado'";

        $stmtProd = $db->prepare($sqlProd);

        $params = array_merge([$pedido], $equipamentosPrincipais);
        $stmtProd->execute($params);
        $totalPendentes = (int)$stmtProd->fetchColumn();
    }

    if ($totalPendentes === 0) {

        $sqlCheck = 'SELECT COUNT(*) FROM pedidos_prontos WHERE numero_pedido = ?';
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute([$pedido]);
        $existe = $stmtCheck->fetchColumn();

        if (!$existe) {
            if ($isOS) {
                $stmtPrazo = $db->prepare("SELECT prazo_producao FROM itens_os WHERE numero_pedido = ?");
                $stmtPrazo->execute([$pedido]);
                $prazoOriginal = $stmtPrazo->fetchColumn();

                $prazo = $prazoOriginal ? trim($prazoOriginal) : 'Sem prazo';
            } else {
                $stmtPrazo = $db->prepare("SELECT `PRAZO DE PRODUCAO` FROM tabela_adaptada WHERE `NUMERO PEDIDO` = ?");
                $stmtPrazo->execute([$pedido]);
                $prazoOriginal = $stmtPrazo->fetchColumn();

                $prazo = $prazoOriginal ? trim($prazoOriginal) : 'Sem prazo';
            }

            $sqlInsert = "INSERT INTO pedidos_prontos (numero_pedido, prazo_producao, data_conclusao, status_posvenda)
                            VALUES (?, ?, NOW(), 'Financeiro')";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([$pedido, $prazo]);
        }

        echo json_encode(['success' => true, 'status_pedido' => 'SUBIU_POS_VENDA']);
    } else {
        echo json_encode([
            'success' => true,
            'status_pedido' => 'AGUARDANDO_OUTRO_QUADRO',
            'pendentes' => $totalPendentes
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
