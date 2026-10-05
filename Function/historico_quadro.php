<?php
/*
 * Linha do tempo do quadro: o banco registra cada mudança (INSERT/UPDATE/DELETE) de
 * itens_producao, itens_os, tabela_adaptada e pedidos_prontos em historico_quadro, via
 * triggers (nada nas tabelas originais é alterado). Daqui dá pra remontar o estado de
 * qualquer momento depois da instalação.
 */

const HQ_TABELAS = [
    'itens_producao' => 'id',
    'itens_os' => 'id',
    'tabela_adaptada' => 'NUMERO PEDIDO',
    'pedidos_prontos' => 'id',
];

function hqColunas(PDO $db, string $tabela): array
{
    return $db->query("SHOW COLUMNS FROM `$tabela`")->fetchAll(PDO::FETCH_COLUMN);
}

/** Instala a tabela de histórico, o estado-base e os triggers. Idempotente: não duplica. */
function hqInstalar(PDO $db): array
{
    $log = [];
    $db->exec("CREATE TABLE IF NOT EXISTS historico_quadro (
        id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ocorrido_utc DATETIME(3) NOT NULL,
        tabela VARCHAR(30) NOT NULL,
        operacao CHAR(1) NOT NULL COMMENT 'S=base, I=insert, U=update, D=delete',
        chave VARCHAR(80) NOT NULL,
        numero_pedido VARCHAR(80) NULL,
        dados LONGTEXT NULL COMMENT 'linha completa DEPOIS da mudança (null no delete)',
        KEY idx_estado (tabela, chave, ocorrido_utc),
        KEY idx_tempo (ocorrido_utc)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $log[] = 'tabela historico_quadro ok';

    $jaTemBase = (int) $db->query("SELECT COUNT(*) FROM historico_quadro WHERE operacao = 'S'")->fetchColumn() > 0;

    foreach (HQ_TABELAS as $t => $chaveCol) {
        $cols = hqColunas($db, $t);
        $obj = function (string $ref) use ($cols) {
            $p = [];
            foreach ($cols as $c) $p[] = "'" . str_replace("'", "''", $c) . "', $ref.`$c`";
            return 'JSON_OBJECT(' . implode(', ', $p) . ')';
        };
        $ped = in_array('numero_pedido', $cols, true) ? 'numero_pedido' : '`NUMERO PEDIDO`';
        // tabela_adaptada não tem id e pode ter 2 linhas com o mesmo pedido: a chave inclui um hash da linha
        $chave = fn(string $ref) => $t === 'tabela_adaptada'
            ? "CONCAT(CAST($ref.`NUMERO PEDIDO` AS CHAR), '#', MD5(" . $obj($ref) . "))"
            : "CAST($ref.`$chaveCol` AS CHAR)";

        if (!$jaTemBase) {
            $db->exec("INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                       SELECT UTC_TIMESTAMP(3), '$t', 'S', " . $chave('src') . ", CAST($ped AS CHAR), " . $obj('src') . " FROM `$t` src");
        }

        foreach (['hq_%s_ai', 'hq_%s_au', 'hq_%s_ad'] as $padrao) $db->exec('DROP TRIGGER IF EXISTS ' . sprintf($padrao, $t));

        $db->exec("CREATE TRIGGER hq_{$t}_ai AFTER INSERT ON `$t` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), '$t', 'I', " . $chave('NEW') . ", CAST(NEW.$ped AS CHAR), " . $obj('NEW') . ")");

        $db->exec("CREATE TRIGGER hq_{$t}_au AFTER UPDATE ON `$t` FOR EACH ROW
            BEGIN
              DECLARE novo LONGTEXT; DECLARE velho LONGTEXT;
              SET novo = " . $obj('NEW') . ";
              SET velho = " . $obj('OLD') . ";
              IF NOT (novo <=> velho) THEN
                IF NOT ((" . $chave('OLD') . ") <=> (" . $chave('NEW') . ")) THEN
                  INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                  VALUES (UTC_TIMESTAMP(3), '$t', 'D', " . $chave('OLD') . ", CAST(OLD.$ped AS CHAR), NULL);
                END IF;
                INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                VALUES (UTC_TIMESTAMP(3), '$t', 'U', " . $chave('NEW') . ", CAST(NEW.$ped AS CHAR), novo);
              END IF;
            END");

        $db->exec("CREATE TRIGGER hq_{$t}_ad AFTER DELETE ON `$t` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), '$t', 'D', " . $chave('OLD') . ", CAST(OLD.$ped AS CHAR), NULL)");

        $log[] = "$t: " . ($jaTemBase ? 'base já existia' : 'estado-base gravado') . ', 3 triggers criados';
    }
    return $log;
}

/** Primeiro instante registrado (hora local de São Paulo) ou null. */
function hqInicioRegistro(PDO $db): ?DateTime
{
    $v = $db->query("SELECT MIN(ocorrido_utc) FROM historico_quadro")->fetchColumn();
    if (!$v) return null;
    return (new DateTime($v, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/Sao_Paulo'));
}

/**
 * Cria TABELAS TEMPORÁRIAS com o mesmo nome das reais (somente nesta conexão) preenchidas com o estado
 * do banco no momento $local. Depois disso o Sistema/views leem o passado sem saber. Nada é gravado nas tabelas reais.
 */
function hqMontarEstadoEm(PDO $db, DateTime $local): void
{
    $inicio = hqInicioRegistro($db);
    if (!$inicio) throw new RuntimeException('O registro de histórico ainda não foi instalado.');
    if ($local < $inicio) throw new RuntimeException('Sem histórico antes de ' . $inicio->format('d/m/Y H:i') . '.');

    $utc = (clone $local)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.v');

    $colunas = []; $ddl = [];
    foreach (HQ_TABELAS as $t => $_) { // antes de sombrear
        $colunas[$t] = hqColunas($db, $t);
        $ddl[$t] = $db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1];
    }

    foreach ($colunas as $t => $cols) {
        $db->exec(preg_replace('/^CREATE TABLE /', 'CREATE TEMPORARY TABLE ', $ddl[$t]));
        $sel = [];
        foreach ($cols as $c) $sel[] = "JSON_VALUE(h.dados, '$.\"" . str_replace('"', '\\"', $c) . "\"')";
        $sql = "INSERT INTO `$t` (`" . implode('`, `', $cols) . "`)
                SELECT " . implode(', ', $sel) . "
                FROM historico_quadro h
                JOIN (SELECT chave, MAX(id) mid FROM historico_quadro WHERE tabela = ? AND ocorrido_utc <= ? GROUP BY chave) m ON m.mid = h.id
                WHERE h.operacao <> 'D'";
        $db->prepare($sql)->execute([$t, $utc]);
    }
}
