<?php
/*
 * Linha do tempo "por pedido": uma matriz pedido x item x dia, mostrando o status de cada item no fim de cada dia
 * (❌ pendente, ✅ produzido [Q = qualidade], E embalado, A armazenado), do prazo (ou do primeiro dia produzido, se antes) até hoje.
 * Dias a partir do início do registro usam o histórico exato (historico_quadro); dias anteriores são estimados
 * pelas datas gravadas no próprio item (data_inicio = produzido, data_fim = embalado, data_armazem = armazenado) e saem esmaecidos.
 */
require_once '../Function/trava.php';
verificarAcessoSetor(CARGOS_LINHA_DO_TEMPO);
require_once '../config/Database.php';
require_once '../Model/Sistema.php';
require_once '../Function/historico_quadro.php';
date_default_timezone_set('America/Sao_Paulo');

const LT_MAX_DIAS = 35;     // quantos dias para trás, no máximo
const LT_MAX_PEDIDOS = 60;  // quantos pedidos por tela

$db = (new Database())->getConnection();
$q = trim($_GET['q'] ?? '');
$agora = new DateTime('now');
$hoje = new DateTime('today');
$erro = null;
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

try {
    $inicioReg = hqInicioRegistro($db);
    if (!$inicioReg) throw new RuntimeException('O registro de histórico ainda não foi instalado.');

    // 1) quais pedidos
    $pedidos = [];
    if ($q === '') {
        $sis = new Sistema($db);
        foreach (array_merge($sis->mostrarTabela(), $sis->mostrarTabelaClassico()) as $l) $pedidos[$l['numero']] = true;
        $pedidos = array_keys($pedidos);
    } else {
        foreach (preg_split('/[\s,;]+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $termo) {
            $st = $db->prepare("(SELECT DISTINCT numero_pedido FROM itens_producao WHERE numero_pedido LIKE ?) UNION (SELECT DISTINCT numero_pedido FROM itens_os WHERE numero_pedido LIKE ?) LIMIT " . LT_MAX_PEDIDOS);
            $st->execute(["%$termo%", "%$termo%"]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $p) $pedidos[$p] = true;
        }
        $pedidos = array_keys($pedidos);
        usort($pedidos, 'strnatcasecmp');
    }
    $pedidos = array_slice($pedidos, 0, LT_MAX_PEDIDOS);

    // 2) itens reais desses pedidos
    $itens = [];
    if ($pedidos) {
        $in = implode(',', array_fill(0, count($pedidos), '?'));
        foreach (['itens_producao', 'itens_os'] as $tab) {
            $st = $db->prepare("SELECT id, numero_pedido, equipamento, posicao_no_pedido, prazo_producao, status, status_qualidade, qualidade_tentativas, data_inicio, data_fim, data_armazem
                                FROM $tab WHERE numero_pedido IN ($in) AND equipamento NOT LIKE 'Emb.%' ORDER BY numero_pedido, equipamento, posicao_no_pedido");
            $st->execute($pedidos);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $r['tab'] = $tab; $itens[] = $r; }
        }
    }

    // 3) intervalo de dias
    $inicio = clone $hoje;
    $prazoDe = [];
    foreach ($itens as $r) {
        $pz = DateTime::createFromFormat('d/m/Y', substr((string) $r['prazo_producao'], 0, 10));
        if ($pz) { $pz->setTime(0, 0); $prazoDe[$r['numero_pedido']] = $pz; if ($pz < $inicio) $inicio = clone $pz; }
        if ($r['data_inicio']) { $d = (new DateTime($r['data_inicio']))->setTime(0, 0); if ($d < $inicio) $inicio = $d; }
    }
    $limite = (clone $hoje)->modify('-' . (LT_MAX_DIAS - 1) . ' days');
    $cortado = $inicio < $limite;
    if ($cortado) $inicio = $limite;
    $dias = [];
    for ($d = clone $inicio; $d <= $hoje; $d->modify('+1 day')) $dias[] = clone $d;

    // 4) eventos do histórico por item
    $eventos = [];
    if ($pedidos) {
        $in = implode(',', array_fill(0, count($pedidos), '?'));
        $st = $db->prepare("SELECT tabela, chave, operacao, ocorrido_utc, JSON_VALUE(dados, '$.status') AS st, JSON_VALUE(dados, '$.status_qualidade') AS sq
                            FROM historico_quadro WHERE tabela IN ('itens_producao', 'itens_os') AND numero_pedido IN ($in) ORDER BY id");
        $st->execute($pedidos);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $e) $eventos[$e['tabela'] . ':' . $e['chave']][] = $e;
    }
} catch (Throwable $e) {
    $erro = $e->getMessage();
    error_log('linha_do_tempo_pedidos: ' . $e->getMessage());
}

/** Estado do item no instante $fim (DateTime local). Retorna [status, status_qualidade, estimado] ou null se o item não existia. */
function lt_estado(array $item, array $ev, DateTime $fim, DateTime $inicioReg): ?array
{
    if ($fim >= $inicioReg) {
        $utc = (clone $fim)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.999');
        $ult = null;
        foreach ($ev as $e) { if ($e['ocorrido_utc'] <= $utc) $ult = $e; else break; }
        if (!$ult || $ult['operacao'] === 'D') return null;
        return [$ult['st'], $ult['sq'], false];
    }
    // antes do registro: estimativa pelas datas do próprio item
    $f = $fim->format('Y-m-d H:i:s');
    if ($item['data_armazem'] && $item['data_armazem'] <= $f) return ['Armazenado', 'N/A', true];
    if ($item['data_fim'] && $item['data_fim'] <= $f) return ['Embalado', 'N/A', true];
    if ($item['data_inicio'] && $item['data_inicio'] <= $f) return ['Produzido', 'N/A', true];
    return ['Pendente', 'N/A', true];
}

/** Uma célula: ícone (1 unidade) ou contador/chips (várias). */
function lt_celula(array $estados): string
{
    $cor = ['X' => '#e11d48', 'P' => '#16a34a', 'PA' => '#a16207', 'PV' => '#2a7a4f', 'Q' => '#c0392b', 'E' => '#27ae60', 'A' => '#2980b9'];
    $rot = ['X' => '❌', 'P' => '✅', 'PA' => '✅', 'PV' => '✅', 'Q' => '✅', 'E' => 'E', 'A' => 'A'];
    $selo = fn($k) => in_array($k, ['PA', 'PV', 'Q'], true) ? '<i class="q" style="background:' . ($k === 'PV' ? '#2a7a4f' : ($k === 'Q' ? '#c0392b' : '#dfd54d')) . '">Q</i>' : '';
    $g = [];
    foreach ($estados as $s) {
        if ($s === null) continue;
        [$st, $sq] = $s;
        if ($st === 'Pendente') $k = $sq === 'Reprovado' ? 'Q' : 'X';
        elseif ($st === 'Produzido') $k = in_array($sq, ['N/A', '', null], true) ? 'P' : ($sq === 'Aprovado' ? 'PV' : 'PA');
        elseif ($st === 'Embalado') $k = 'E';
        elseif ($st === 'Armazenado') $k = 'A';
        else $k = 'X';
        $g[$k] = ($g[$k] ?? 0) + 1;
    }
    if (!$g) return '<span class="vz">-</span>';
    $o = '';
    foreach ($g as $k => $n) $o .= '<b style="color:' . $cor[$k] . '">' . ($n > 1 ? $n : '') . $rot[$k] . $selo($k) . '</b>';
    return '<span class="cel">' . $o . '</span>';
}

$semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linha do tempo por pedido</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f4f7f6; color: #0f172a; }
        .barra { position: sticky; top: 0; z-index: 5; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; padding: 10px 14px; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .barra form { display: flex; gap: 6px; }
        .barra input[type=text] { font: inherit; font-size: 17px; padding: 9px 14px; border: 1px solid #94a3b8; border-radius: 8px; width: 340px; }
        .barra button, .barra a.btn { font: inherit; font-size: 16px; font-weight: 600; border: 0; border-radius: 8px; padding: 9px 18px; background: #1e3a8a; color: #fff; cursor: pointer; text-decoration: none; }
        .barra a.btn.sec { background: #e2e8f0; color: #0f172a; }
        .info { color: #64748b; font-size: 15px; }
        .aviso { margin: 14px; padding: 12px 16px; background: #fef2f2; color: #b91c1c; border-radius: 8px; font-weight: 600; }
        .nota { margin: 10px 14px 0; color: #475569; font-size: 15px; line-height: 1.5; }
        .wrap { overflow: auto; padding: 0 14px 14px; margin-top: 10px; max-height: calc(100vh - 190px); }
        table { border-collapse: separate; border-spacing: 0; background: #fff; font-size: 20px; }
        th, td { border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 10px 14px; text-align: center; white-space: nowrap; }
        thead th { position: sticky; top: 0; background: #e2e8f0; z-index: 3; font-size: 17px; }
        th.hoje { background: #fde68a; }
        td.p, th.p { position: sticky; left: 0; background: #fff; z-index: 2; text-align: left; font-weight: 700; min-width: 120px; }
        td.i, th.i { position: sticky; left: 120px; background: #fff; z-index: 2; text-align: left; min-width: 290px; border-right: 2px solid #94a3b8; }
        thead th.p, thead th.i { background: #cbd5e1; z-index: 4; }
        tr.novo td { border-top: 2px solid #475569; }
        td.hoje { background: #fffbeb; }
        td.est { opacity: .5; }
        .cel { display: inline-flex; gap: 5px; align-items: center; justify-content: center; font-size: 28px; }
        .cel b { background: #f1f5f9; border-radius: 8px; padding: 1px 8px; white-space: nowrap; font-size: 24px; }
        i.q { display: inline-flex; align-items: center; justify-content: center; color: #fff; font-style: normal; font-size: 15px; font-weight: 700; border-radius: 50%; width: 25px; height: 25px; margin-left: 3px; vertical-align: middle; }
        .vz { color: #cbd5e1; }
        .prazo { color: #b45309; font-size: 15px; font-weight: 600; }
        td.prz { box-shadow: inset 0 -4px 0 #f59e0b; }
    </style>
</head>

<body>
    <div class="barra">
        <form method="get">
            <input type="text" name="q" value="<?= $h($q) ?>" placeholder="Pesquisar pedido (ex.: 8693, OS 4205)" autofocus>
            <button type="submit">Pesquisar</button>
            <?php if ($q !== ''): ?><a class="btn sec" href="?">Pedidos no quadro</a><?php endif; ?>
        </form>
        <span class="info"><?= $q === '' ? 'Mostrando os pedidos que estão hoje nos quadros' : 'Resultado da pesquisa' ?><?= isset($pedidos) ? ' · ' . count($pedidos) . ' pedido(s)' : '' ?></span>
    </div>

    <?php if ($erro): ?>
        <div class="aviso"><?= $h($erro) ?></div>
    <?php elseif (!$pedidos): ?>
        <div class="aviso">Nenhum pedido encontrado.</div>
    <?php else: ?>
        <div class="nota">
            ❌ pendente · ✅ produzido (Q amarelo = aguardando inspeção, Q verde = aprovado, Q vermelho = reprovado) · E embalado · A armazenado · cada coluna mostra o status no fim do dia · barra laranja = dia do prazo.
            Dias <b>esmaecidos</b> são anteriores ao início do registro (<?= $h($inicioReg->format('d/m H:i')) ?>) e foram estimados pelas datas gravadas no item.
            <?= $cortado ? ' Limitado aos últimos ' . LT_MAX_DIAS . ' dias.' : '' ?>
            <?= count($pedidos) >= LT_MAX_PEDIDOS ? ' Mostrando só os primeiros ' . LT_MAX_PEDIDOS . ' pedidos; refine a pesquisa.' : '' ?>
        </div>
        <div class="wrap">
            <table>
                <thead>
                    <tr>
                        <th class="p">Pedido</th>
                        <th class="i">Item</th>
                        <?php foreach ($dias as $d): $eh = $d == $hoje; ?>
                            <th class="<?= $eh ? 'hoje' : '' ?>"><?= $eh ? 'Hoje' : $d->format('d/m') ?><br><small style="font-size:14px"><?= $semana[(int) $d->format('w')] ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // agrupa unidades do mesmo equipamento numa linha
                    $linhas = [];
                    foreach ($itens as $r) $linhas[$r['numero_pedido']][$r['equipamento']][] = $r;
                    $ordem = array_flip($pedidos);
                    uksort($linhas, fn($a, $b) => ($ordem[$a] ?? 9999) <=> ($ordem[$b] ?? 9999));
                    foreach ($linhas as $num => $equips):
                        $primeira = true;
                        foreach ($equips as $nomeEq => $unidades):
                            $pz = $prazoDe[$num] ?? null;
                    ?>
                            <tr class="<?= $primeira ? 'novo' : '' ?>">
                                <td class="p"><?= $primeira ? $h($num) : '' ?><?php if ($primeira && $pz): ?><br><span class="prazo">prazo <?= $pz->format('d/m') ?></span><?php endif; ?></td>
                                <td class="i"><?= $h($nomeEq) ?><?= count($unidades) > 1 ? ' <small>×' . count($unidades) . '</small>' : '' ?></td>
                                <?php foreach ($dias as $d):
                                    $fim = (clone $d)->setTime(23, 59, 59);
                                    if ($fim > $agora) $fim = clone $agora;
                                    $estados = []; $est = false;
                                    foreach ($unidades as $u) {
                                        $s = lt_estado($u, $eventos[$u['tab'] . ':' . $u['id']] ?? [], $fim, $inicioReg);
                                        if ($s) { $estados[] = [$s[0], $s[1]]; $est = $est || $s[2]; }
                                    }
                                    $classe = trim(($d == $hoje ? 'hoje ' : '') . ($est ? 'est ' : '') . ($pz && $d == $pz ? 'prz' : ''));
                                ?>
                                    <td class="<?= $classe ?>"><?= lt_celula($estados) ?></td>
                                <?php endforeach; ?>
                            </tr>
                    <?php $primeira = false; endforeach; endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>

</html>
