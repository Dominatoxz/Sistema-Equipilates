<?php
header('Content-Type: application/json');
require_once '../config/Database.php';
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
        $stmtCheck = $db->prepare("SELECT * FROM $tabelaAlvo WHERE id = ?");
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
                $novoStatus = 'Produzido';
                $podeAtualizar = true;
            } else {
                echo json_encode(['success' => false, 'error' => 'Este item já foi fabricado!']);
                exit;
            }
        } elseif ($tipo === 'E') {
            if ($statusAtual === 'Produzido') {
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
                $idRealRetorno = ($tabelaAlvo === 'itens_os') ? 'OS' . $id : $id;

                $nrPedidoDoBanco = $item['numero_pedido'] ?? $item['numero_os'] ?? $item['numero'] ?? 'Desconhecido';
                $nomeEquipamentoDoBanco = $item['equipamento'] ?? 'Equipamento';

                $arquivo_cache = __DIR__ . '/../cache/dados_painel.json';
                $arquivo_cache_os = __DIR__ . '/../cache/dados_painelOs.json'; 

                if (file_exists($arquivo_cache)) {
                    unlink($arquivo_cache);
                }
                if (file_exists($arquivo_cache_os)) {
                    unlink($arquivo_cache_os); 
                }

                echo json_encode([
                    'success' => true, 
                    'idReal' => $idRealRetorno, 
                    'statusGerado' => $novoStatus,
                    'pedidoReal' => $nrPedidoDoBanco,
                    'equipamentoReal' => $nomeEquipamentoDoBanco
                ]);
                exit;
            }
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Dados inválidos ou incompletos']);
exit;