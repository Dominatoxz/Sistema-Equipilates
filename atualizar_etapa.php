<?php
header('Content-Type: application/json');
require_once './config/Database.php';
$db = (new Database())->getConnection();

$codigoLido = $_GET['id'] ?? null;

if ($codigoLido) {
    $partes = explode('-', $codigoLido);
    $idBruto = $partes[0] ?? null;   
    $tipo = $partes[1] ?? null; 

    if (strpos(strtoupper($idBruto),  'OS') === 0){
        $tabelaAlvo = 'itens_os';

        $id = (int) substr($idBruto, 2);
    } else {
        $tabelaAlvo = 'itens_producao';
        $id = (int) $idBruto;
    }

    if ($idBruto && $tipo) {

        $stmtCheck = $db->prepare("SELECT status FROM $tabelaAlvo WHERE id = ?");
        $stmtCheck->execute([$id]);
        $item = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        
        if (!$item) {
            echo json_encode(['success' => false, 'error' => 'Item não encontrado no banco']);
            exit;
        }

        $statusAtual = trim($item['status']); 

        $podeAtualizar = false;
        $novoStatus = '';

        if ($tipo === 'P') {
            if ($statusAtual === 'Pendente') {
                $novoStatus = 'Finalizado';
                $podeAtualizar = true;
            } else {
                echo json_encode(['success' => false, 'error' => 'Este item já foi fabricado!']);
                exit;
            }
        } elseif ($tipo === 'E') {
            if ($statusAtual === 'Finalizado') {
                $novoStatus = 'Embalado';
                $podeAtualizar = true;
            } else {

                echo json_encode(['success' => false, 'error' => 'Não é possível embalar um item que não foi fabricado!']);
                exit;
            }
        }

        if ($podeAtualizar) {
            $query = "UPDATE $tabelaAlvo SET status = :status, data_fim = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $novoStatus);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'idReal' => $id, 
                    'statusGerado' => $novoStatus
                ]);
                exit;
            }
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Dados inválidos ou incompletos']);
exit;