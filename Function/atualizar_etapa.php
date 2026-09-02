<?php
require_once '../Function/trava.php';
header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../Function/notificar_pos_producao.php';
require_once '../Function/notificar_qualidade.php';
require_once '../Model/Sistema.php';
$db = (new Database())->getConnection();

date_default_timezone_set('America/Sao_Paulo');
// Gate de qualidade só vale pra item marcado Produzido a partir desta data —
// itens já em produção antes disso seguem o fluxo antigo, direto pro embalo.
const DATA_CORTE_QUALIDADE = '2026-09-03';
$gateQualidadeAtivo = date('Y-m-d') >= DATA_CORTE_QUALIDADE;

// Inspeção de qualidade só vale pra equipamento principal (tabela.php /
// tabela_classico.php) — acessório nunca entra no gate.
$equipamentosComGateQualidade = array_merge(
    Sistema::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO,
    Sistema::EQUIPAMENTOS_PRINCIPAIS_CLASSICO
);

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
                    // Só bloqueia quem realmente entrou no gate (Aguardando/Reprovado).
                    // 'N/A' é item de antes da virada de chave ou com o gate
                    // desligado — segue liberado, como sempre foi.
                    $statusQualidadeAtual = $item['status_qualidade'] ?? 'N/A';
                    if (in_array($statusQualidadeAtual, ['Aguardando', 'Reprovado'], true)) {
                        $mensagemBloqueio = ($statusQualidadeAtual === 'Reprovado')
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
            $dataHoraPHP = date('Y-m-d H:i:s');

            $ehItemComGateQualidade = in_array(trim($item['equipamento'] ?? ''), $equipamentosComGateQualidade, true);
            $entraNoGateQualidade = $novoStatus === 'Produzido' && $gateQualidadeAtivo && $ehItemComGateQualidade;
            $setQualidade = $entraNoGateQualidade ? ", status_qualidade = 'Aguardando'" : '';
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

                if ($entraNoGateQualidade) {
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
