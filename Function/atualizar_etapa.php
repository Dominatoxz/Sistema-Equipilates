<?php
require_once '../Function/trava.php';
header('Content-Type: application/json');
require_once '../config/Database.php';
$db = (new Database())->getConnection();

$codigoLido = $_GET['id'] ?? null;
$origem = $_GET['origem'] ?? 'producao';

if ($codigoLido) {
    $partes = explode('-', $codigoLido);
    $idBruto = $partes[0] ?? null;
    $tipo = $partes[1] ?? null;

    if (strpos(strtoupper($idBruto),  'OS') === 0) {
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
        $colunaData = '';

        if ($tipo === 'P') {
            if ($origem !== 'producao') {
                echo json_encode(['success' => false, 'error' => 'Ação não permitida nesta tela.']);
                exit;
            }
            if ($statusAtual === 'Pendente') {
                $novoStatus = 'Produzido';
                $colunaData = 'data_inicio';
                $podeAtualizar = true;
            } else {
                echo json_encode(['success' => false, 'error' => 'Este item já foi fabricado!']);
                exit;
            }
        } elseif ($tipo === 'E') {
            if ($origem === 'expedicao') {
                if ($statusAtual === 'Embalado') {
                    $novoStatus = 'Armazenado';
                    $colunaData = 'data_armazem';
                    $podeAtualizar = true;
                } elseif ($statusAtual === 'Armazenado') {
                    echo json_encode(['success' => false, 'error' => 'Este item já foi armazenado!']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Este item ainda não foi embalado na produção!']);
                    exit;
                }
            } else {
                if ($statusAtual === 'Produzido') {
                    $novoStatus = 'Embalado';
                    $colunaData = 'data_fim';
                    $podeAtualizar = true;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Não é possível embalar um item que não foi fabricado!']);
                    exit;
                }
            }
        }

        if ($podeAtualizar) {
            date_default_timezone_set('America/Sao_Paulo');
            $dataHoraPHP = date('Y-m-d H:i:s');

            if ($colunaData !== '') {
                $query = "UPDATE $tabelaAlvo SET status = :status, $colunaData = :data_registro WHERE id = :id";
            } else {
                $query = "UPDATE $tabelaAlvo SET status = :status WHERE id = :id";
            }
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $novoStatus);
            if ($colunaData !== '') {
                $stmt->bindParam(':data_registro', $dataHoraPHP);
            }
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $idRealRetorno = ($tabelaAlvo === 'itens_os') ? 'OS' . $id : $id;

                $nrPedidoDoBanco = $item['numero_pedido'] ?? $item['numero_os'] ?? $item['numero'] ?? 'Desconhecido';
                $nomeEquipamentoDoBanco = $item['equipamento'] ?? 'Equipamento';

                foreach (glob(__DIR__ . '/../cache/*.json') as $arquivoCache) {
                    unlink($arquivoCache);
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
