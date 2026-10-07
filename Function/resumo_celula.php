<?php
/*
 * Resumo de uma célula do quadro quando o item tem mais de 3 unidades: um status só vira contador ("6E", "5❌");
 * status misturados viram chips ("2❌ 1✅ 3E"). Usado pelos quadros e pelas telas de acessórios.
 * Devolve '' quando são 3 unidades ou menos (aí cada peça mostra seu próprio ícone).
 *
 * $pecas: linhas com status (e, se $comQualidade, status_qualidade, qualidade_tentativas, ultima_decisao).
 * Chaves de grupo: X pendente, Q reprovado, W retrabalho, P produzido, PA produzido aguardando inspeção,
 * PV produzido aprovado, E embalado, A armazenado.
 */
function resumoCelulaItens(array $pecas, int $fonteContador, bool $comQualidade = true, string $rotuloX = '❌'): string
{
    if (count($pecas) <= 3) {
        return '';
    }
    $grupos = ['X' => 0, 'Q' => 0, 'W' => 0, 'P' => 0, 'PA' => 0, 'PV' => 0, 'E' => 0, 'A' => 0];
    foreach ($pecas as $pc) {
        if ($comQualidade && $pc['status'] === 'Pendente' && ((int) ($pc['qualidade_tentativas'] ?? 0)) > 0) $kg = (($pc['ultima_decisao'] ?? null) === 'Retrabalho') ? 'W' : 'Q';
        elseif ($pc['status'] === 'Produzido') {
            if ($comQualidade) $kg = in_array($pc['status_qualidade'] ?? 'N/A', ['N/A', ''], true) ? 'P' : (($pc['status_qualidade'] ?? '') === 'Aprovado' ? 'PV' : 'PA');
            else $kg = 'P';
        }
        elseif ($pc['status'] === 'Embalado') $kg = 'E';
        elseif ($pc['status'] === 'Armazenado') $kg = 'A';
        else $kg = 'X';
        $grupos[$kg]++;
    }
    $grupos = array_filter($grupos);
    $rotG = ['X' => $rotuloX, 'Q' => 'Q', 'W' => '⚠️', 'P' => '✅', 'PA' => '✅', 'PV' => '✅', 'E' => 'E', 'A' => 'A'];
    $corG = ['X' => '#e11d48', 'Q' => '#c0392b', 'W' => '#d97706', 'P' => '#16a34a', 'PA' => '#a16207', 'PV' => '#2a7a4f', 'E' => '#27ae60', 'A' => '#2980b9'];
    $dicaG = ['X' => 'pendente', 'Q' => 'reprovado', 'W' => 'retrabalho', 'P' => 'produzido', 'PA' => 'produzido, aguardando inspeção', 'PV' => 'produzido e aprovado', 'E' => 'embalado', 'A' => 'armazenado'];
    $seloG = fn($k, $d) => in_array($k, ['PA', 'PV'], true) ? '<span style="display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;margin-left:3px;background:' . ($k === 'PV' ? '#2a7a4f' : '#dfd54d') . ';color:#fff;font-size:' . $d . 'px;font-weight:bold;border-radius:50%;width:' . ($d + 8) . 'px;height:' . ($d + 8) . 'px;line-height:1;">Q</span>' : '';
    if (count($grupos) === 1) {
        $kg = array_key_first($grupos);
        return '<span class="resumo-celula" title="' . $dicaG[$kg] . '" style="color:' . $corG[$kg] . ';font-weight:bold;font-size:' . $fonteContador . 'px;">' . $grupos[$kg] . $rotG[$kg] . $seloG($kg, 16) . '</span>';
    }
    $chips = '';
    foreach ($grupos as $kg => $n) {
        $chips .= '<span title="' . $dicaG[$kg] . '" style="background:' . $corG[$kg] . '1f;color:' . $corG[$kg] . ';border:1px solid ' . $corG[$kg] . '55;border-radius:6px;padding:0 5px;font-weight:bold;font-size:15px;white-space:nowrap;">' . $n . $rotG[$kg] . $seloG($kg, 12) . '</span>';
    }
    return '<span class="resumo-celula" style="display:inline-flex;flex-wrap:wrap;gap:3px;justify-content:center;align-items:center;">' . $chips . '</span>';
}
