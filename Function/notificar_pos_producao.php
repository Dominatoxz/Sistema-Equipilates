<?php
require_once '../Function/trava.php';
require_once '../config/Database.php';
require_once '../Function/disparar_notificacao.php';
require_once '../Function/cargos.php';

/*
 * Ponto único que decide quando um pedido "fecha a produção" e sobe pra
 * fila do Financeiro (tabela pedidos_prontos). Antes existia duplicado em
 * notificar_posVenda.php e notificar_posVenda_classico.php (Contemporâneo
 * e Clássico com o mesmo código copiado) — unificado aqui em 2026-08 junto
 * com a mudança de fluxo: agora o gatilho é "todo item Embalado ou
 * Armazenado" (antes exigia todo item Armazenado). A tela de Expedição
 * continua controlando o status Armazenado à parte; ver dar_baixa_expedicao.php
 * para o bloqueio de "Finalizar pedido" que ainda exige tudo Armazenado.
 *
 * Também dispara a notificação Web Push pro Financeiro aqui mesmo, no
 * servidor — antes disso ficava a cargo do JS de cada tela perceber que a
 * própria lista mudou e chamar disparar_novo_pedido.php, o que só
 * funcionava se alguém do setor de origem estivesse com aquela tela aberta
 * bem na hora. Ver Function/atualizar_etapa.php, que chama esta função a
 * cada item marcado Embalado/Armazenado (é o único lugar que sempre roda,
 * não importa qual tela disparou o clique).
 *
 * $pedido: número do pedido (ex: "12345" ou "OS123").
 * Retorna array pronto pra json_encode.
 */
function notificarPosProducao(PDO $db, ?string $pedido): array
{
    if (!$pedido) {
        return ['success' => false, 'error' => 'Pedido não informado.'];
    }

    try {
        $isOS = (stripos($pedido, 'os') !== false);
        $tabelaItens = $isOS ? 'itens_os' : 'itens_producao';

        // Gaiola Cadilac não conta mais pra fechar o pedido: desde 2026-08 ela
        // deixou de estar vinculada a um pedido específico e virou contador
        // agregado de quantidade pendente (ver Sistema::contarGaiolasCadilac*).
        $sql = "SELECT COUNT(*) FROM $tabelaItens
                WHERE numero_pedido = ?
                  AND equipamento NOT LIKE 'Emb.%'
                  AND equipamento != 'Gaiola Cadilac'
                  AND status NOT IN ('Embalado', 'Armazenado')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$pedido]);
        $totalPendentes = (int) $stmt->fetchColumn();

        if ($totalPendentes === 0) {
            $sqlCheck = 'SELECT COUNT(*) FROM pedidos_prontos WHERE numero_pedido = ?';
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$pedido]);
            $existe = $stmtCheck->fetchColumn();

            if (!$existe) {
                if ($isOS) {
                    $stmtPrazo = $db->prepare("SELECT prazo_producao FROM itens_os WHERE numero_pedido = ?");
                } else {
                    $stmtPrazo = $db->prepare("SELECT `PRAZO DE PRODUCAO` FROM tabela_adaptada WHERE `NUMERO PEDIDO` = ?");
                }
                $stmtPrazo->execute([$pedido]);
                $prazoOriginal = $stmtPrazo->fetchColumn();
                $prazo = $prazoOriginal ? trim($prazoOriginal) : 'Sem prazo';

                $sqlInsert = "INSERT INTO pedidos_prontos (numero_pedido, prazo_producao, data_conclusao, status_posvenda)
                                VALUES (?, ?, NOW(), 'Financeiro')";
                $stmtInsert = $db->prepare($sqlInsert);
                $stmtInsert->execute([$pedido, $prazo]);

                enviarNotificacaoPorSetor(
                    CARGOS_FINANCEIRO_ACAO,
                    "Novo Pedido no Financeiro! 💰",
                    "O pedido {$pedido} acabou de chegar na fila do Financeiro.",
                    "../View/tabela_financeiro.php"
                );
            }

            return ['success' => true, 'status_pedido' => 'SUBIU_POS_VENDA'];
        }

        return [
            'success' => true,
            'status_pedido' => 'AGUARDANDO_OUTRO_QUADRO',
            'pendentes' => $totalPendentes
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Content-Type: application/json');
    $database = new Database();
    $db = $database->getConnection();
    echo json_encode(notificarPosProducao($db, $_GET['pedido'] ?? null));
}
