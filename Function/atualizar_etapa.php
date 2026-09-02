<?php
require_once '../Function/trava.php';
header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../Function/notificar_pos_producao.php';
require_once '../Function/notificar_qualidade.php';
$db = (new Database())->getConnection();

$codigoLido = $_GET['id'] ?? null;
$origem = $_GET['origem'] ?? 'producao';

if ($codigoLido) {
    $partes = explode('-', $codigoLido);
    $idBruto = $partes[0] ?? null;
    $tipo = $partes[1] ?? null;
    // Etiquetas de reimpressão (pós-reprovação da qualidade) vêm como -PQ/-EQ;
    // só o primeiro caractere importa pra máquina de estados abaixo.
    $tipoBase = $tipo ? strtoupper($tipo[0]) : null;

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

        if ($tipoBase === 'P') {
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
        } elseif ($tipoBase === 'E') {
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
                $nomeEquipamentoAtual = trim($item['equipamento'] ?? '');

                if ($statusAtual === 'Produzido') {
                    if (($item['status_qualidade'] ?? 'N/A') !== 'Aprovado') {
                        $mensagemBloqueio = ($item['status_qualidade'] ?? '') === 'Reprovado'
                            ? 'Item reprovado pela qualidade — aguardando nova produção.'
                            : 'Item aguardando aprovação da qualidade.';
                        echo json_encode(['success' => false, 'error' => $mensagemBloqueio]);
                        exit;
                    }
                    $novoStatus = 'Embalado';
                    $colunaData = 'data_fim';
                    $podeAtualizar = true;
                } elseif ($statusAtual === 'Pendente' && $nomeEquipamentoAtual === 'Gaiola Cadilac') {
                    // Gaiola Cadilac não tem etiqueta física de produção — ela
                    // entra no sistema já considerada "produzida", então a
                    // primeira bipagem (etiqueta de embalagem) já leva direto
                    // pra Embalado, sem passar por Produzido.
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

            $setQualidade = ($novoStatus === 'Produzido') ? ", status_qualidade = 'Aguardando'" : '';
            if ($colunaData !== '') {
                $query = "UPDATE $tabelaAlvo SET status = :status, $colunaData = :data_registro$setQualidade WHERE id = :id AND status = :status_esperado";
            } else {
                $query = "UPDATE $tabelaAlvo SET status = :status$setQualidade WHERE id = :id AND status = :status_esperado";
            }
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $novoStatus);
            if ($colunaData !== '') {
                $stmt->bindParam(':data_registro', $dataHoraPHP);
            }
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status_esperado', $statusAtual);

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'error' => 'Falha ao atualizar o item no banco.']);
                exit;
            }

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'error' => 'Este item já foi atualizado por outra bipagem. Recarregue a tela e tente novamente.']);
                exit;
            }

            {
                $idRealRetorno = ($tabelaAlvo === 'itens_os') ? 'OS' . $id : $id;

                $nrPedidoDoBanco = $item['numero_pedido'] ?? $item['numero_os'] ?? $item['numero'] ?? 'Desconhecido';
                $nomeEquipamentoDoBanco = $item['equipamento'] ?? 'Equipamento';

                foreach (glob(__DIR__ . '/../cache/*.json') as $arquivoCache) {
                    unlink($arquivoCache);
                }

                $notificacaoPosProducao = null;
                if (in_array($novoStatus, ['Embalado', 'Armazenado'], true) && $nrPedidoDoBanco !== 'Desconhecido') {
                    $notificacaoPosProducao = notificarPosProducao($db, (string) $nrPedidoDoBanco);
                }

                if ($novoStatus === 'Produzido') {
                    notificarQualidade($db, $tabelaAlvo, $id);
                }

                echo json_encode([
                    'success' => true,
                    'idReal' => $idRealRetorno,
                    'statusGerado' => $novoStatus,
                    'pedidoReal' => $nrPedidoDoBanco,
                    'equipamentoReal' => $nomeEquipamentoDoBanco,
                    'posProducao' => $notificacaoPosProducao
                ]);
                exit;
            }
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Dados inválidos ou incompletos']);
exit;
