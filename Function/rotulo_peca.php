<?php
/*
 * Rótulo de peça: marca um item como "só uma peça" do equipamento (ex.: BASE do Reformer numa OS),
 * sem mudar o nome do equipamento — assim o quadro e a bipagem seguem iguais e só a etiqueta
 * mostra o rótulo (ex.: "BASE - Reformer Excellence"). Guardado em item_rotulo_peca
 * (tabela_origem 'PRODUCAO'/'OS' + item_id); sem linha lá, o item é tratado como inteiro.
 */

/** Rótulo do item ('' se não tiver, ou se a tabela ainda não existir). */
function rotuloPeca(?PDO $db, string $tabelaOrigem, int $itemId): string
{
    static $cache = [];
    if (!$db) return '';
    $k = $tabelaOrigem . ':' . $itemId;
    if (!array_key_exists($k, $cache)) {
        try {
            $st = $db->prepare('SELECT rotulo FROM item_rotulo_peca WHERE tabela_origem = ? AND item_id = ?');
            $st->execute([$tabelaOrigem, $itemId]);
            $cache[$k] = (string) ($st->fetchColumn() ?: '');
        } catch (Throwable $e) {
            // 1146 = tabela ainda não existe (normal antes da instalação); qualquer outro erro vai pro log
            if (($e instanceof PDOException ? ($e->errorInfo[1] ?? 0) : 0) !== 1146) error_log('rotulo_peca: ' . $e->getMessage());
            $cache[$k] = '';
        }
    }
    return $cache[$k];
}

/** Nome do equipamento com o rótulo na frente quando o item é uma peça. $item precisa de id, tabela_origem e equipamento. */
function nomeComRotulo(array $item, ?PDO $db = null): string
{
    $db = $db ?: ($GLOBALS['db'] ?? null);
    $r = rotuloPeca($db, (string) ($item['tabela_origem'] ?? ''), (int) ($item['id'] ?? 0));
    return $r !== '' ? $r . ' - ' . $item['equipamento'] : (string) $item['equipamento'];
}
