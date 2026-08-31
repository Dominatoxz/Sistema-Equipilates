<?php
require_once __DIR__ . '/../Function/trava.php';
date_default_timezone_set('America/Sao_Paulo');
class Sistema
{
    private $conn;

    const CORTE_EXPEDICAO_CONTEMPORANEO = '2026-08-04';

    /*
     * Gaiola Cadilac (Contemporâneo) e Gaiola Classico / Gaiola Cadilac
     * Tauari (Clássico) são fisicamente o mesmo item — só entram no sistema
     * com nomes diferentes por conta da linha do pedido. Toda a família de
     * métodos de Gaiola Cadilac abaixo conta os três juntos, num total único
     * por tabela de itens (produção normal x OS), independente da linha.
     */
    const EQUIPAMENTOS_GAIOLA_CADILAC = ['Gaiola Cadilac', 'GAIOLA CLASSICO', 'GAIOLA CADILCAC TAUARI'];

    const EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO = [
        'Reformer Excellence',
        'Reformer Torre',
        'Cadilac Excelence',
        'Step Chair Excelence',
        'Lader Barrel Excelence',
        'Wall Unit',
        'Carrinho Excellence',
        'Carrinho Torre',
    ];

    const EQUIPAMENTOS_PRINCIPAIS_CLASSICO = [
        'REF. CLASSICO ALUMINIO',
        'CARRINHO CLASSICO',
        'REF. CLASSICO TORRE',
        'CARRINHO CLASSICO TORRE',
        'CAD. CLASSICO ALUMINIO',
        'GAIOLA CLASSICO',
        'REF. CLASSICO TAUARI',
        'CARRINHO CLASSICO TAUARI',
        'CAD. CLASSICO TAUARI',
        'GAIOLA CADILCAC TAUARI',
        'REFORMER HIBRIDO',
        'CARRINHO CLASSICO HIBRIDO',
        'WUNDA CHAIR',
        'ELECTRIC CHAIR',
        'ARM CHAIR',
        'LADDER BARREL CLÁSS.',
        'PEDI O POLE',
        'WALL UNIT CLÁSSICO',
        'MAT CLÁSSICO',
        'MAT PORTÁTIL',
        'BENCH MAT',
        'GUILHOTINA',
    ];

    const ACESSORIOS_CONTEMPORANEO = [
        'Caixa Mini',
        'Caixa do Reformer',
        'P. de Molas - B R I N D E',
        'P. de Molas - C O M P L E T A',
        'P. de Molas - P u s h T h r u',
        'Caixa da Cadeira',
        'Prancha de Alongamento',
        'SPINE CORRECTOR',
        'SMALL BARREL',
        'BASTÃO ALUMÍNIO 1,5 M',
        'PUSH UP DEVICE (PAR)',
    ];

    const ACESSORIOS_CLASSICO = [
        'CAIXA DO REFORMER CLÁSSICA',
        'SUPORTE SPINE CORRECTOR',
        'MINI EXTENSÃO MOVE FLOW',
        'PLATAFORMA BARREL CLÁSSICO',
        'BARRA PUSH TRUE (BALANÇO CLASSICO)',
        'SPACER BOX',
        '2 x 4 (TWO BY FOUR)',
        'KUNA BOARD',
        'TRAVESSEIRO BENCH MAT',
        'TRAVESSEIRO RÉGUA',
        'TRAVESSEIRO 1/2 LUA',
        'TRAV. CILINDRICO',
        'TRAV. OMBREIRA (PAR)',
        'TRAV. CABEC. 30 mm',
        'TRAV. CABEC. 40 mm',
        'CAPA PROT. BARREL CLÁSS.',
        'SHEEPSKIN COVER',
        'PUXADOR DE ALUMINIO',
        'ANEL DE PILATES ARCHIVE AÇO',
        'MAGIC SQUARE',
        'FOOT CORREC. ALUM.',
        'BEAN BAG',
        'BREATH A CIZER',
        'NECK STRETCHER',
        'HAND TENS O METER',
        'TOE EXERCISER',
        'AIR PLANE BOARD',
        'FINGER EXERCISE',
        'MINI BARREL',
        'MINI SPINE',
    ];

    private function condicaoItemPendente(string $colunaPedido, array $equipamentos, string $tabelaItens = 'itens_producao'): string
    {
        $placeholders = implode(',', array_fill(0, count($equipamentos), '?'));
        return "(
                    NOT EXISTS (
                        SELECT 1 FROM $tabelaItens ip
                        WHERE ip.numero_pedido = $colunaPedido AND ip.equipamento IN ($placeholders)
                    )
                    OR EXISTS (
                        SELECT 1 FROM $tabelaItens ip
                        WHERE ip.numero_pedido = $colunaPedido AND ip.equipamento IN ($placeholders)
                          AND ip.status NOT IN ('Embalado', 'Armazenado')
                    )
                )";
    }

    private function condicaoAlgumEmbaladoOuArmazenado(string $colunaPedido, array $equipamentos, string $tabelaItens = 'itens_producao'): string
    {
        $placeholders = implode(',', array_fill(0, count($equipamentos), '?'));
        return "EXISTS (
                    SELECT 1 FROM $tabelaItens ip
                    WHERE ip.numero_pedido = $colunaPedido AND ip.equipamento IN ($placeholders)
                      AND ip.status IN ('Embalado', 'Armazenado')
                )";
    }

    private function condicaoAindaNaoTudoArmazenado(string $colunaPedido, array $equipamentos, string $tabelaItens = 'itens_producao'): string
    {
        $placeholders = implode(',', array_fill(0, count($equipamentos), '?'));
        return "EXISTS (
                    SELECT 1 FROM $tabelaItens ip
                    WHERE ip.numero_pedido = $colunaPedido AND ip.equipamento IN ($placeholders)
                      AND ip.status != 'Armazenado'
                )";
    }

    public function __construct($db)
    {
        $this->conn = $db;
    }
    /*
     * Começo (segunda) e fim (domingo) da semana ISO que contém $referencia.
     * Usado por toda a família de métodos de Gaiola Cadilac abaixo.
     */
    private function limitesSemana(DateTime $referencia): array
    {
        $diaSemanaIso = (int) $referencia->format('N'); // 1 = segunda ... 7 = domingo
        $inicio = (clone $referencia)->modify('-' . ($diaSemanaIso - 1) . ' days')->format('Y-m-d');
        $fim = (clone $referencia)->modify('+' . (7 - $diaSemanaIso) . ' days')->format('Y-m-d');
        return [$inicio, $fim];
    }

    /*
     * Planejado x Real "crus" de uma semana pra Gaiola Cadilac:
     *   - "planejado" = quantas gaiolas tinham prazo_producao dentro dessa
     *     semana (a meta) — baseado na DATA PREVISTA.
     *   - "real" = quantas gaiolas foram DE FATO embaladas (bipadas) dentro
     *     dessa semana, usando data_fim (quando a bipagem realmente
     *     aconteceu) — não o prazo original. É isso que permite Real >
     *     Planejado: se a produção bipar gaiolas atrasadas de semanas
     *     passadas nesta semana, elas contam aqui como produção real desta
     *     semana, mesmo com prazo antigo.
     */
    private function planejadoRealSemana(string $tabelaItens, string $inicioSemana, string $fimSemana, array $equipamentos = self::EQUIPAMENTOS_GAIOLA_CADILAC): array
    {
        $inicioSemanaDT = $inicioSemana . ' 00:00:00';
        $fimSemanaDT = $fimSemana . ' 23:59:59';
        $placeholdersEquip = implode(',', array_fill(0, count($equipamentos), '?'));

        $stmt = $this->conn->prepare(
            "SELECT
                SUM(CASE WHEN STR_TO_DATE(prazo_producao, '%d/%m/%Y') BETWEEN ? AND ? THEN 1 ELSE 0 END) AS planejado,
                SUM(CASE WHEN status IN ('Embalado', 'Armazenado') AND data_fim BETWEEN ? AND ? THEN 1 ELSE 0 END) AS `real`
             FROM $tabelaItens
             WHERE equipamento IN ($placeholdersEquip)"
        );
        $stmt->execute(array_merge(
            [$inicioSemana, $fimSemana, $inicioSemanaDT, $fimSemanaDT],
            $equipamentos
        ));
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'planejado' => (int) ($linha['planejado'] ?? 0),
            'real' => (int) ($linha['real'] ?? 0),
        ];
    }

    /*
     * Planejado / Real / Atrasados de Gaiola Cadilac da semana ATUAL
     * (segunda a domingo), nas telas de produção (normal e OS) — número
     * agregado, não vinculado a nenhum pedido específico, e somando as três
     * linhas juntas (Contemporâneo + Clássico + Clássico Tauari — ver
     * EQUIPAMENTOS_GAIOLA_CADILAC), já que fisicamente é o mesmo item. Ver
     * planejadoRealSemana() pra definição exata de "planejado"/"real".
     *   - "atrasados" = saldo acumulado gravado toda semana pelo fechamento
     *     de sábado (ver fecharSemanaGaiolaCadilac() /
     *     Function/fechar_semana_gaiola.php, chamado por cron) — não é
     *     calculado ao vivo aqui, só lido de gaiola_atrasos_semanais.
     */
    public function contarGaiolasCadilacProducao(string $tabelaItens = 'itens_producao'): array
    {
        [$inicioSemana, $fimSemana] = $this->limitesSemana(new DateTime('today'));
        $numeros = $this->planejadoRealSemana($tabelaItens, $inicioSemana, $fimSemana);
        $numeros['atrasados'] = $this->totalAtrasoAcumuladoGaiola($tabelaItens);
        return $numeros;
    }

    /*
     * Fecha a semana atual de Gaiola Cadilac (soma das três linhas — ver
     * EQUIPAMENTOS_GAIOLA_CADILAC): calcula planejado - real dessa semana
     * (ver planejadoRealSemana()) e grava (ou atualiza, se já rodou) uma
     * linha em gaiola_atrasos_semanais. Feito pra rodar uma vez por semana
     * via cron (todo sábado) — ver Function/fechar_semana_gaiola.php.
     * `$deficit` nunca fica negativo — se a semana produziu igual ou mais
     * que o planejado, só grava 0 (não desconta do total acumulado; ele só
     * soma, nunca diminui).
     */
    public function fecharSemanaGaiolaCadilac(string $tabelaItens = 'itens_producao'): array
    {
        [$inicioSemana, $fimSemana] = $this->limitesSemana(new DateTime('today'));
        $numeros = $this->planejadoRealSemana($tabelaItens, $inicioSemana, $fimSemana);
        $planejado = $numeros['planejado'];
        $real = $numeros['real'];
        $deficit = max(0, $planejado - $real);

        $stmtGrava = $this->conn->prepare(
            "INSERT INTO gaiola_atrasos_semanais (tabela_itens, semana_inicio, semana_fim, planejado, `real`, deficit)
             VALUES (:tabela, :inicio, :fim, :planejado, :real, :deficit)
             ON DUPLICATE KEY UPDATE planejado = :planejado2, `real` = :real2, deficit = :deficit2, semana_fim = :fim2"
        );
        $stmtGrava->execute([
            'tabela' => $tabelaItens,
            'inicio' => $inicioSemana,
            'fim' => $fimSemana,
            'planejado' => $planejado,
            'real' => $real,
            'deficit' => $deficit,
            'planejado2' => $planejado,
            'real2' => $real,
            'deficit2' => $deficit,
            'fim2' => $fimSemana,
        ]);

        return ['semana_inicio' => $inicioSemana, 'semana_fim' => $fimSemana, 'planejado' => $planejado, 'real' => $real, 'deficit' => $deficit];
    }

    /*
     * Soma de todos os fechamentos semanais gravados (ver
     * fecharSemanaGaiolaCadilac()) — só acumula. Uma semana em dia ou
     * acima do planejado grava déficit 0 e não desconta nada do total.
     */
    public function totalAtrasoAcumuladoGaiola(string $tabelaItens = 'itens_producao'): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(deficit), 0) FROM gaiola_atrasos_semanais WHERE tabela_itens = ?"
        );
        $stmt->execute([$tabelaItens]);
        return max(0, (int) $stmt->fetchColumn());
    }

    /*
     * Total de Gaiola Cadilac (soma das três linhas — ver
     * EQUIPAMENTOS_GAIOLA_CADILAC) já embaladas mas ainda não
     * conferidas/armazenadas no galpão — soma itens_producao e itens_os
     * porque a tela de Expedição mistura pedidos normais e de OS na mesma
     * listagem.
     */
    public function contarGaiolasCadilacPendentesArmazenagem(): int
    {
        $placeholders = implode(',', array_fill(0, count(self::EQUIPAMENTOS_GAIOLA_CADILAC), '?'));
        $stmt = $this->conn->prepare(
            "SELECT
                (SELECT COUNT(*) FROM itens_producao WHERE equipamento IN ($placeholders) AND status != 'Armazenado') +
                (SELECT COUNT(*) FROM itens_os WHERE equipamento IN ($placeholders) AND status != 'Armazenado') AS total"
        );
        $stmt->execute(array_merge(self::EQUIPAMENTOS_GAIOLA_CADILAC, self::EQUIPAMENTOS_GAIOLA_CADILAC));
        return (int) $stmt->fetchColumn();
    }

    //função de mostrar a tabela com todos os seus dados
    public function mostrarTabela()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO, self::ACESSORIOS_CONTEMPORANEO);
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', $todosItens);

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Reformer Excellence`,
                         `Reformer Torre`,
                         `Cadilac Excelence`,
                         `Step Chair Excelence`,
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Espelho de mostrarTabela(), mas pro lado da Expedição: mostra o pedido
     * assim que o PRIMEIRO item principal ou acessório da linha Contemporânea
     * é embalado na produção — ele continua aparecendo em mostrarTabela()
     * também até o último item ser embalado, e some daqui só quando TODO
     * item do pedido vira Armazenado (não depende mais de o pedido já ter
     * subido pro Financeiro — as duas filas correm em paralelo).
     */
    public function mostrarTabelaExpedicaoContemporaneo()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO, self::ACESSORIOS_CONTEMPORANEO);
        $condicaoProntoNormal = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_producao');
        $condicaoProntoOs = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_os');
        $condicaoNaoTudoArmazenadoNormal = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_producao');
        $condicaoNaoTudoArmazenadoOs = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_os');

        // Pedidos normais e de OS caem na mesma tela de Expedição — só muda
        // qual tabela de itens (itens_producao/itens_os) é checada.
        $presenca = "(
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )";

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Reformer Excellence`,
                         `Reformer Torre`,
                         `Cadilac Excelence`,
                         `Step Chair Excelence`,
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND $presenca
                  AND STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') >= '" . self::CORTE_EXPEDICAO_CONTEMPORANEO . "'
                  AND $condicaoProntoNormal
                  AND $condicaoNaoTudoArmazenadoNormal

                  UNION ALL

                  SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Reformer Excellence`,
                         `Reformer Torre`,
                         `Cadilac Excelence`,
                         `Step Chair Excelence`,
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND $presenca
                  AND STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') >= '" . self::CORTE_EXPEDICAO_CONTEMPORANEO . "'
                  AND $condicaoProntoOs
                  AND $condicaoNaoTudoArmazenadoOs

                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens, $todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaAcessorios()
    {
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO);

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Wall Unit`,
                         `Caixa Mini`,
                         `caixa do reformer`,
                         `P. de Molas - B R I N D E`,
                         `P. de Molas - C O M P L E T A`,
                         `P. de Molas - P u s h T h r u`,
                         `Caixa da cadeira`,
                         `prancha de alongamento`
                  FROM tabela_adaptada
                  WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%'
                    AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                    AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`caixa do reformer`), '') IS NOT NULL AND TRIM(`caixa do reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da cadeira`), '') IS NOT NULL AND TRIM(`Caixa da cadeira`) != '0') OR
                        (NULLIF(TRIM(`prancha de alongamento`), '') IS NOT NULL AND TRIM(`prancha de alongamento`) != '0')
                    )
                    AND $condicaoPendente
                  ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(self::ACESSORIOS_CONTEMPORANEO, self::ACESSORIOS_CONTEMPORANEO));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Espelho de mostrarTabelaAcessorios() pro lado da Expedição: mostra o
     * pedido assim que o primeiro acessório da linha Contemporânea é
     * embalado na produção, até que todos sejam conferidos/armazenados.
     */
    public function mostrarTabelaExpedicaoContemporaneoAcessorios()
    {
        $condicaoProntoNormal = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO, 'itens_producao');
        $condicaoProntoOs = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO, 'itens_os');
        $condicaoNaoTudoArmazenadoNormal = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO, 'itens_producao');
        $condicaoNaoTudoArmazenadoOs = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO, 'itens_os');

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Wall Unit`,
                         `Caixa Mini`,
                         `caixa do reformer`,
                         `P. de Molas - B R I N D E`,
                         `P. de Molas - C O M P L E T A`,
                         `P. de Molas - P u s h T h r u`,
                         `Caixa da cadeira`,
                         `prancha de alongamento`
                  FROM tabela_adaptada
                  WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%'
                    AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`caixa do reformer`), '') IS NOT NULL AND TRIM(`caixa do reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da cadeira`), '') IS NOT NULL AND TRIM(`Caixa da cadeira`) != '0') OR
                        (NULLIF(TRIM(`prancha de alongamento`), '') IS NOT NULL AND TRIM(`prancha de alongamento`) != '0')
                    )
                    AND STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') >= '" . self::CORTE_EXPEDICAO_CONTEMPORANEO . "'
                    AND $condicaoProntoNormal
                    AND $condicaoNaoTudoArmazenadoNormal

                  UNION ALL

                  SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Wall Unit`,
                         `Caixa Mini`,
                         `caixa do reformer`,
                         `P. de Molas - B R I N D E`,
                         `P. de Molas - C O M P L E T A`,
                         `P. de Molas - P u s h T h r u`,
                         `Caixa da cadeira`,
                         `prancha de alongamento`
                  FROM tabela_adaptada
                  WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%'
                    AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`caixa do reformer`), '') IS NOT NULL AND TRIM(`caixa do reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da cadeira`), '') IS NOT NULL AND TRIM(`Caixa da cadeira`) != '0') OR
                        (NULLIF(TRIM(`prancha de alongamento`), '') IS NOT NULL AND TRIM(`prancha de alongamento`) != '0')
                    )
                    AND STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') >= '" . self::CORTE_EXPEDICAO_CONTEMPORANEO . "'
                    AND $condicaoProntoOs
                    AND $condicaoNaoTudoArmazenadoOs

                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(
            self::ACESSORIOS_CONTEMPORANEO,
            self::ACESSORIOS_CONTEMPORANEO,
            self::ACESSORIOS_CONTEMPORANEO,
            self::ACESSORIOS_CONTEMPORANEO
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function mostrarTabelaOs()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO, self::ACESSORIOS_CONTEMPORANEO);
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', $todosItens, 'itens_os');

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Reformer Excellence`,
                         `Reformer Torre`,
                         `Cadilac Excelence`,
                         `Step Chair Excelence`,
                         `Lader Barrel Excelence`,
                         'Carrinho',
                         'Gaiola'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`Reformer Excellence`), '') IS NOT NULL AND TRIM(`Reformer Excellence`) != '0') OR
                        (NULLIF(TRIM(`Reformer Torre`), '') IS NOT NULL AND TRIM(`Reformer Torre`) != '0') OR
                        (NULLIF(TRIM(`Cadilac Excelence`), '') IS NOT NULL AND TRIM(`Cadilac Excelence`) != '0') OR
                        (NULLIF(TRIM(`Step Chair Excelence`), '') IS NOT NULL AND TRIM(`Step Chair Excelence`) != '0') OR
                        (NULLIF(TRIM(`Lader Barrel Excelence`), '') IS NOT NULL AND TRIM(`Lader Barrel Excelence`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaAcessoriosOs()
    {
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', self::ACESSORIOS_CONTEMPORANEO, 'itens_os');

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `Wall Unit`,
                         `Caixa Mini`,
                         `Caixa do Reformer`,
                         `P. de Molas - B R I N D E`,
                         `P. de Molas - C O M P L E T A`,
                         `P. de Molas - P u s h T h r u`,
                         `Caixa da Cadeira`,
                         `Prancha de Alongamento`
                  FROM tabela_adaptada
                  WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%'
                    AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                    AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                    AND (
                        (NULLIF(TRIM(`Wall Unit`), '') IS NOT NULL AND TRIM(`Wall Unit`) != '0') OR
                        (NULLIF(TRIM(`Caixa Mini`), '') IS NOT NULL AND TRIM(`Caixa Mini`) != '0') OR
                        (NULLIF(TRIM(`Caixa do Reformer`), '') IS NOT NULL AND TRIM(`Caixa do Reformer`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - B R I N D E`), '') IS NOT NULL AND TRIM(`P. de Molas - B R I N D E`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - C O M P L E T A`), '') IS NOT NULL AND TRIM(`P. de Molas - C O M P L E T A`) != '0') OR
                        (NULLIF(TRIM(`P. de Molas - P u s h T h r u`), '') IS NOT NULL AND TRIM(`P. de Molas - P u s h T h r u`) != '0') OR
                        (NULLIF(TRIM(`Caixa da Cadeira`), '') IS NOT NULL AND TRIM(`Caixa da Cadeira`) != '0') OR
                        (NULLIF(TRIM(`Prancha de Alongamento`), '') IS NOT NULL AND TRIM(`Prancha de Alongamento`) != '0')
                    )
                    AND $condicaoPendente
                  ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(self::ACESSORIOS_CONTEMPORANEO, self::ACESSORIOS_CONTEMPORANEO));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaFinanceiro()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Financeiro'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaPosVenda()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Pós-venda'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaExpedicao()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, data_conclusao
                  FROM pedidos_prontos
                  WHERE status_posvenda = 'Expedicao'
                  ORDER BY data_conclusao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassico()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CLASSICO, self::ACESSORIOS_CLASSICO);
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', $todosItens);

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `REF. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TORRE`,
                         `CAD. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TAUARI`,
                         `CAD. CLASSICO TAUARI`,
                         'REFORMER HIBRIDO',
                         'WUNDA CHAIR',
                         'ELECTRIC CHAIR',
                         'ARM CHAIR',
                         'LADDER BARREL CLÁSS.',
                         'PEDI O POLE',
                         'WALL UNIT CLÁSSICO',
                         'MAT CLÁSSICO',
                         'MAT PORTÁTIL',
                         'BENCH MAT',
                         'GUILHOTINA',
                         'CARRINHO',
                         'GAIOLA'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`REF. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`REF. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TORRE`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TORRE`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`REFORMER HIBRIDO`), '') IS NOT NULL AND TRIM(`REFORMER HIBRIDO`) != '0') OR
                        (NULLIF(TRIM(`WUNDA CHAIR`), '') IS NOT NULL AND TRIM(`WUNDA CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ELECTRIC CHAIR`), '') IS NOT NULL AND TRIM(`ELECTRIC CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ARM CHAIR`), '') IS NOT NULL AND TRIM(`ARM CHAIR`) != '0') OR
                        (NULLIF(TRIM(`LADDER BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`LADDER BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`PEDI O POLE`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`WALL UNIT CLÁSSICO`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT CLÁSSICO`), '') IS NOT NULL AND TRIM(`MAT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT PORTÁTIL`), '') IS NOT NULL AND TRIM(`MAT PORTÁTIL`) != '0') OR
                        (NULLIF(TRIM(`BENCH MAT`), '') IS NOT NULL AND TRIM(`BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`GUILHOTINA`), '') IS NOT NULL AND TRIM(`GUILHOTINA`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Espelho de mostrarTabelaClassico() pro lado da Expedição: mostra o
     * pedido assim que o PRIMEIRO item principal ou acessório da linha
     * Clássica é embalado na produção — ele continua aparecendo em
     * mostrarTabelaClassico() também até o último item ser embalado, e some
     * daqui só quando TODO item do pedido vira Armazenado (não depende mais
     * de o pedido já ter subido pro Financeiro — as duas filas correm em
     * paralelo).
     */
    public function mostrarTabelaExpedicaoClassico()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CLASSICO, self::ACESSORIOS_CLASSICO);
        $condicaoProntoNormal = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_producao');
        $condicaoProntoOs = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_os');
        $condicaoNaoTudoArmazenadoNormal = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_producao');
        $condicaoNaoTudoArmazenadoOs = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', $todosItens, 'itens_os');

        $colunas = "`NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `REF. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TORRE`,
                         `CAD. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TAUARI`,
                         `CAD. CLASSICO TAUARI`,
                         'REFORMER HIBRIDO',
                         'WUNDA CHAIR',
                         'ELECTRIC CHAIR',
                         'ARM CHAIR',
                         'LADDER BARREL CLÁSS.',
                         'PEDI O POLE',
                         'WALL UNIT CLÁSSICO',
                         'MAT CLÁSSICO',
                         'MAT PORTÁTIL',
                         'BENCH MAT',
                         'GUILHOTINA',
                         'CARRINHO',
                         'GAIOLA'";

        $presenca = "(
                        (NULLIF(TRIM(`REF. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`REF. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TORRE`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TORRE`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`REFORMER HIBRIDO`), '') IS NOT NULL AND TRIM(`REFORMER HIBRIDO`) != '0') OR
                        (NULLIF(TRIM(`WUNDA CHAIR`), '') IS NOT NULL AND TRIM(`WUNDA CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ELECTRIC CHAIR`), '') IS NOT NULL AND TRIM(`ELECTRIC CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ARM CHAIR`), '') IS NOT NULL AND TRIM(`ARM CHAIR`) != '0') OR
                        (NULLIF(TRIM(`LADDER BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`LADDER BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`PEDI O POLE`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`WALL UNIT CLÁSSICO`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT CLÁSSICO`), '') IS NOT NULL AND TRIM(`MAT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT PORTÁTIL`), '') IS NOT NULL AND TRIM(`MAT PORTÁTIL`) != '0') OR
                        (NULLIF(TRIM(`BENCH MAT`), '') IS NOT NULL AND TRIM(`BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`GUILHOTINA`), '') IS NOT NULL AND TRIM(`GUILHOTINA`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0')
                    )";

        $query = "SELECT $colunas
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND $presenca
                  AND $condicaoProntoNormal
                  AND $condicaoNaoTudoArmazenadoNormal

                  UNION ALL

                  SELECT $colunas
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND $presenca
                  AND $condicaoProntoOs
                  AND $condicaoNaoTudoArmazenadoOs

                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens, $todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassicoAcessorios()
    {
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO);

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao, 
                         `CAIXA DO REFORMER CLÁSSICA`,
                         `SPINE CORRECTOR`, 
                         `SMALL BARREL`, 
                         `SUPORTE SPINE CORRECTOR`, 
                         `MINI EXTENSÃO MOVE FLOW`, 
                         `PLATAFORMA BARREL CLÁSSICO`,
                         'BARRA PUSH TRUE (BALANÇO CLASSICO)',
                         'SPACER BOX',
                         '2 x 4 (TWO BY FOUR)',
                         'KUNA BOARD',
                         'TRAVESSEIRO BENCH MAT',
                         'TRAVESSEIRO RÉGUA',
                         'TRAVESSEIRO 1/2 LUA',
                         'TRAV. CILINDRICO',
                         'TRAV. OMBREIRA (PAR)',
                         'TRAV. CABEC. 30 mm',
                         'TRAV. CABEC. 40 mm',
                         'CAPA PROT. BARREL CLÁSS.',
                         'SHEEPSKIN COVER',
                         'BASTÃO ALUMÍNIO 1,5 M',
                         'PUXADOR DE ALUMINIO',
                         'ANEL DE PILATES ARCHIVE AÇO',
                         'MAGIC SQUARE',
                         'FOOT CORREC. ALUM.',
                         'BEAN BAG',
                         'BREATH A CIZER',
                         'NECK STRETCHER',
                         'HAND TENS O METER',
                         'TOE EXERCISER',
                         'AIR PLANE BOARD',
                         'FINGER EXERCISE',
                         'PUSH UP DEVICE (PAR)',
                         'MINI BARREL',
                         'MINI SPINE'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`CAIXA DO REFORMER CLÁSSICA`), '') IS NOT NULL AND TRIM(`CAIXA DO REFORMER CLÁSSICA`) != '0') OR
                        (NULLIF(TRIM(`SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`SMALL BARREL`), '') IS NOT NULL AND TRIM(`SMALL BARREL`) != '0') OR
                        (NULLIF(TRIM(`SUPORTE SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SUPORTE SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`MINI EXTENSÃO MOVE FLOW`), '') IS NOT NULL AND TRIM(`MINI EXTENSÃO MOVE FLOW`) != '0') OR
                        (NULLIF(TRIM(`PLATAFORMA BARREL CLÁSSICO`), '') IS NOT NULL AND TRIM(`PLATAFORMA BARREL CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`), '') IS NOT NULL AND TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`) != '0') OR
                        (NULLIF(TRIM(`SPACER BOX`), '') IS NOT NULL AND TRIM(`SPACER BOX`) != '0') OR
                        (NULLIF(TRIM(`2 x 4 (TWO BY FOUR)`), '') IS NOT NULL AND TRIM(`2 x 4 (TWO BY FOUR)`) != '0') OR
                        (NULLIF(TRIM(`KUNA BOARD`), '') IS NOT NULL AND TRIM(`KUNA BOARD`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO BENCH MAT`), '') IS NOT NULL AND TRIM(`TRAVESSEIRO BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO RÉGUA`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO 1/2 LUA`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CILINDRICO`), '') IS NOT NULL AND TRIM(`TRAV. CILINDRICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. OMBREIRA (PAR)`), '') IS NOT NULL AND TRIM(`TRAV. OMBREIRA (PAR)`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 30 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 30 mm`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 40 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 40 mm`) != '0') OR
                        (NULLIF(TRIM(`CAPA PROT. BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`CAPA PROT. BARREL CLÁSS.`) != '0') OR 
                        (NULLIF(TRIM(`SHEEPSKIN COVER`), '') IS NOT NULL AND TRIM(`SHEEPSKIN COVER`) != '0') OR
                        (NULLIF(TRIM(`BASTÃO ALUMÍNIO 1,5 M`), '') IS NOT NULL AND TRIM(`BASTÃO ALUMÍNIO 1,5 M`) != '0') OR
                        (NULLIF(TRIM(`PUXADOR DE ALUMINIO`), '') IS NOT NULL AND TRIM(`PUXADOR DE ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`ANEL DE PILATES ARCHIVE AÇO`), '') IS NOT NULL AND TRIM(`ANEL DE PILATES ARCHIVE AÇO`) != '0') OR
                        (NULLIF(TRIM(`MAGIC SQUARE`), '') IS NOT NULL AND TRIM(`MAGIC SQUARE`) != '0') OR
                        (NULLIF(TRIM(`FOOT CORREC. ALUM.`), '') IS NOT NULL AND TRIM(`FOOT CORREC. ALUM.`) != '0') OR
                        (NULLIF(TRIM(`BEAN BAG`), '') IS NOT NULL AND TRIM(`BEAN BAG`) != '0') OR
                        (NULLIF(TRIM(`BREATH A CIZER`), '') IS NOT NULL AND TRIM(`BREATH A CIZER`) != '0') OR
                        (NULLIF(TRIM(`NECK STRETCHER`), '') IS NOT NULL AND TRIM(`NECK STRETCHER`) != '0') OR
                        (NULLIF(TRIM(`HAND TENS O METER`), '') IS NOT NULL AND TRIM(`HAND TENS O METER`) != '0') OR
                        (NULLIF(TRIM(`TOE EXERCISER`), '') IS NOT NULL AND TRIM(`TOE EXERCISER`) != '0') OR
                        (NULLIF(TRIM(`AIR PLANE BOARD`), '') IS NOT NULL AND TRIM(`AIR PLANE BOARD`) != '0') OR
                        (NULLIF(TRIM(`PUSH UP DEVICE (PAR)`), '') IS NOT NULL AND TRIM(`PUSH UP DEVICE (PAR)`) != '0') OR
                        (NULLIF(TRIM(`MINI BARREL`), '') IS NOT NULL AND TRIM(`MINI BARREL`) != '0') OR
                        (NULLIF(TRIM(`MINI SPINE`), '') IS NOT NULL AND TRIM(`MINI SPINE`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(self::ACESSORIOS_CLASSICO, self::ACESSORIOS_CLASSICO));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * Espelho de mostrarTabelaClassicoAcessorios() pro lado da Expedição:
     * mostra o pedido assim que o primeiro acessório da linha Clássica é
     * embalado na produção, até que todos sejam conferidos/armazenados.
     */
    public function mostrarTabelaExpedicaoClassicoAcessorios()
    {
        $condicaoProntoNormal = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO, 'itens_producao');
        $condicaoProntoOs = $this->condicaoAlgumEmbaladoOuArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO, 'itens_os');
        $condicaoNaoTudoArmazenadoNormal = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO, 'itens_producao');
        $condicaoNaoTudoArmazenadoOs = $this->condicaoAindaNaoTudoArmazenado('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO, 'itens_os');

        $colunas = "`NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `CAIXA DO REFORMER CLÁSSICA`,
                         `SPINE CORRECTOR`,
                         `SMALL BARREL`,
                         `SUPORTE SPINE CORRECTOR`,
                         `MINI EXTENSÃO MOVE FLOW`,
                         `PLATAFORMA BARREL CLÁSSICO`,
                         'BARRA PUSH TRUE (BALANÇO CLASSICO)',
                         'SPACER BOX',
                         '2 x 4 (TWO BY FOUR)',
                         'KUNA BOARD',
                         'TRAVESSEIRO BENCH MAT',
                         'TRAVESSEIRO RÉGUA',
                         'TRAVESSEIRO 1/2 LUA',
                         'TRAV. CILINDRICO',
                         'TRAV. OMBREIRA (PAR)',
                         'TRAV. CABEC. 30 mm',
                         'TRAV. CABEC. 40 mm',
                         'CAPA PROT. BARREL CLÁSS.',
                         'SHEEPSKIN COVER',
                         'BASTÃO ALUMÍNIO 1,5 M',
                         'PUXADOR DE ALUMINIO',
                         'ANEL DE PILATES ARCHIVE AÇO',
                         'MAGIC SQUARE',
                         'FOOT CORREC. ALUM.',
                         'BEAN BAG',
                         'BREATH A CIZER',
                         'NECK STRETCHER',
                         'HAND TENS O METER',
                         'TOE EXERCISER',
                         'AIR PLANE BOARD',
                         'FINGER EXERCISE',
                         'PUSH UP DEVICE (PAR)',
                         'MINI BARREL',
                         'MINI SPINE'";

        $presenca = "(
                        (NULLIF(TRIM(`CAIXA DO REFORMER CLÁSSICA`), '') IS NOT NULL AND TRIM(`CAIXA DO REFORMER CLÁSSICA`) != '0') OR
                        (NULLIF(TRIM(`SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`SMALL BARREL`), '') IS NOT NULL AND TRIM(`SMALL BARREL`) != '0') OR
                        (NULLIF(TRIM(`SUPORTE SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SUPORTE SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`MINI EXTENSÃO MOVE FLOW`), '') IS NOT NULL AND TRIM(`MINI EXTENSÃO MOVE FLOW`) != '0') OR
                        (NULLIF(TRIM(`PLATAFORMA BARREL CLÁSSICO`), '') IS NOT NULL AND TRIM(`PLATAFORMA BARREL CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`), '') IS NOT NULL AND TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`) != '0') OR
                        (NULLIF(TRIM(`SPACER BOX`), '') IS NOT NULL AND TRIM(`SPACER BOX`) != '0') OR
                        (NULLIF(TRIM(`2 x 4 (TWO BY FOUR)`), '') IS NOT NULL AND TRIM(`2 x 4 (TWO BY FOUR)`) != '0') OR
                        (NULLIF(TRIM(`KUNA BOARD`), '') IS NOT NULL AND TRIM(`KUNA BOARD`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO BENCH MAT`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO 1/2 LUA`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CILINDRICO`), '') IS NOT NULL AND TRIM(`TRAV. CILINDRICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. OMBREIRA (PAR)`), '') IS NOT NULL AND TRIM(`TRAV. OMBREIRA (PAR)`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 30 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 30 mm`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 40 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 40 mm`) != '0') OR
                        (NULLIF(TRIM(`CAPA PROT. BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`CAPA PROT. BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`SHEEPSKIN COVER`), '') IS NOT NULL AND TRIM(`SHEEPSKIN COVER`) != '0') OR
                        (NULLIF(TRIM(`BASTÃO ALUMÍNIO 1,5 M`), '') IS NOT NULL AND TRIM(`BASTÃO ALUMÍNIO 1,5 M`) != '0') OR
                        (NULLIF(TRIM(`PUXADOR DE ALUMINIO`), '') IS NOT NULL AND TRIM(`PUXADOR DE ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`ANEL DE PILATES ARCHIVE AÇO`), '') IS NOT NULL AND TRIM(`ANEL DE PILATES ARCHIVE AÇO`) != '0') OR
                        (NULLIF(TRIM(`MAGIC SQUARE`), '') IS NOT NULL AND TRIM(`MAGIC SQUARE`) != '0') OR
                        (NULLIF(TRIM(`FOOT CORREC. ALUM.`), '') IS NOT NULL AND TRIM(`FOOT CORREC. ALUM.`) != '0') OR
                        (NULLIF(TRIM(`BEAN BAG`), '') IS NOT NULL AND TRIM(`BEAN BAG`) != '0') OR
                        (NULLIF(TRIM(`BREATH A CIZER`), '') IS NOT NULL AND TRIM(`BREATH A CIZER`) != '0') OR
                        (NULLIF(TRIM(`NECK STRETCHER`), '') IS NOT NULL AND TRIM(`NECK STRETCHER`) != '0') OR
                        (NULLIF(TRIM(`HAND TENS O METER`), '') IS NOT NULL AND TRIM(`HAND TENS O METER`) != '0') OR
                        (NULLIF(TRIM(`TOE EXERCISER`), '') IS NOT NULL AND TRIM(`TOE EXERCISER`) != '0') OR
                        (NULLIF(TRIM(`AIR PLANE BOARD`), '') IS NOT NULL AND TRIM(`AIR PLANE BOARD`) != '0') OR
                        (NULLIF(TRIM(`PUSH UP DEVICE (PAR)`), '') IS NOT NULL AND TRIM(`PUSH UP DEVICE (PAR)`) != '0') OR
                        (NULLIF(TRIM(`MINI BARREL`), '') IS NOT NULL AND TRIM(`MINI BARREL`) != '0') OR
                        (NULLIF(TRIM(`MINI SPINE`), '') IS NOT NULL AND TRIM(`MINI SPINE`) != '0')
                    )";

        $query = "SELECT $colunas
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) NOT LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) NOT LIKE '%os%'
                  AND $presenca
                  AND $condicaoProntoNormal
                  AND $condicaoNaoTudoArmazenadoNormal

                  UNION ALL

                  SELECT $colunas
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND $presenca
                  AND $condicaoProntoOs
                  AND $condicaoNaoTudoArmazenadoOs

                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(
            self::ACESSORIOS_CLASSICO,
            self::ACESSORIOS_CLASSICO,
            self::ACESSORIOS_CLASSICO,
            self::ACESSORIOS_CLASSICO
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarTabelaClassicoOs()
    {
        $todosItens = array_merge(self::EQUIPAMENTOS_PRINCIPAIS_CLASSICO, self::ACESSORIOS_CLASSICO);
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', $todosItens, 'itens_os');

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `REF. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TORRE`,
                         `CAD. CLASSICO ALUMINIO`,
                         `REF. CLASSICO TAUARI`,
                         `CAD. CLASSICO TAUARI`,
                         'REFORMER HIBRIDO',
                         'WUNDA CHAIR',
                         'ELECTRIC CHAIR',
                         'ARM CHAIR',
                         'LADDER BARREL CLÁSS.',
                         'PEDI O POLE',
                         'WALL UNIT CLÁSSICO',
                         'MAT CLÁSSICO',
                         'MAT PORTÁTIL',
                         'BENCH MAT',
                         'GUILHOTINA',
                         'CARRINHO',
                         'GAIOLA'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`REF. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`REF. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TORRE`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TORRE`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO ALUMINIO`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`CAD. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`CAD. CLASSICO TAUARI`) != '0') OR
                        (NULLIF(TRIM(`REFORMER HIBRIDO`), '') IS NOT NULL AND TRIM(`REFORMER HIBRIDO`) != '0') OR
                        (NULLIF(TRIM(`WUNDA CHAIR`), '') IS NOT NULL AND TRIM(`WUNDA CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ELECTRIC CHAIR`), '') IS NOT NULL AND TRIM(`ELECTRIC CHAIR`) != '0') OR
                        (NULLIF(TRIM(`ARM CHAIR`), '') IS NOT NULL AND TRIM(`ARM CHAIR`) != '0') OR
                        (NULLIF(TRIM(`LADDER BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`LADDER BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`PEDI O POLE`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`WALL UNIT CLÁSSICO`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT CLÁSSICO`), '') IS NOT NULL AND TRIM(`MAT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`MAT PORTÁTIL`), '') IS NOT NULL AND TRIM(`MAT PORTÁTIL`) != '0') OR
                        (NULLIF(TRIM(`BENCH MAT`), '') IS NOT NULL AND TRIM(`BENCH MAT`) != '0') OR
                        (NULLIF(TRIM(`GUILHOTINA`), '') IS NOT NULL AND TRIM(`GUILHOTINA`) != '0') OR
                        (NULLIF(TRIM(`REF. CLASSICO TAUARI`), '') IS NOT NULL AND TRIM(`REF. CLASSICO TAUARI`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($todosItens, $todosItens));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function mostrarTabelaClassicoAcessoriosOs()
    {
        $condicaoPendente = $this->condicaoItemPendente('`NUMERO PEDIDO`', self::ACESSORIOS_CLASSICO, 'itens_os');

        $query = "SELECT `NUMERO PEDIDO` as numero,
                         `PRAZO DE PRODUCAO` as prazo_producao,
                         `CAIXA DO REFORMER CLÁSSICA`,
                         `SPINE CORRECTOR`,
                         `SMALL BARREL`,
                         `SUPORTE SPINE CORRECTOR`,
                         `MINI EXTENSÃO MOVE FLOW`,
                         `PLATAFORMA BARREL CLÁSSICO`,
                         'BARRA PUSH TRUE (BALANÇO CLASSICO)',
                         'SPACER BOX',
                         '2 x 4 (TWO BY FOUR)',
                         'KUNA BOARD',
                         'TRAVESSEIRO BENCH MAT',
                         'TRAVESSEIRO RÉGUA',
                         'TRAVESSEIRO 1/2 LUA',
                         'TRAV. CILINDRICO',
                         'TRAV. OMBREIRA (PAR)',
                         'TRAV. CABEC. 30 mm',
                         'TRAV. CABEC. 40 mm',
                         'CAPA PROT. BARREL CLÁSS.',
                         'SHEEPSKIN COVER',
                         'BASTÃO ALUMÍNIO 1,5 M',
                         'PUXADOR DE ALUMINIO',
                         'ANEL DE PILATES ARCHIVE AÇO',
                         'MAGIC SQUARE',
                         'FOOT CORREC. ALUM.',
                         'BEAN BAG',
                         'BREATH A CIZER',
                         'NECK STRETCHER',
                         'HAND TENS O METER',
                         'TOE EXERCISER',
                         'AIR PLANE BOARD',
                         'FINGER EXERCISE',
                         'PUSH UP DEVICE (PAR)',
                         'MINI BARREL',
                         'MINI SPINE'
                  FROM tabela_adaptada WHERE LOWER(`NUMERO PEDIDO`) LIKE 'os%' AND LOWER(`NUMERO PEDIDO`) LIKE '%os%'
                  AND `NUMERO PEDIDO` NOT IN (SELECT numero_pedido FROM pedidos_prontos)
                  AND (
                        (NULLIF(TRIM(`CAIXA DO REFORMER CLÁSSICA`), '') IS NOT NULL AND TRIM(`CAIXA DO REFORMER CLÁSSICA`) != '0') OR
                        (NULLIF(TRIM(`SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`SMALL BARREL`), '') IS NOT NULL AND TRIM(`SMALL BARREL`) != '0') OR
                        (NULLIF(TRIM(`SUPORTE SPINE CORRECTOR`), '') IS NOT NULL AND TRIM(`SUPORTE SPINE CORRECTOR`) != '0') OR
                        (NULLIF(TRIM(`MINI EXTENSÃO MOVE FLOW`), '') IS NOT NULL AND TRIM(`MINI EXTENSÃO MOVE FLOW`) != '0') OR
                        (NULLIF(TRIM(`PLATAFORMA BARREL CLÁSSICO`), '') IS NOT NULL AND TRIM(`PLATAFORMA BARREL CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`), '') IS NOT NULL AND TRIM(`BARRA PUSH TRUE (BALANÇO CLASSICO)`) != '0') OR
                        (NULLIF(TRIM(`SPACER BOX`), '') IS NOT NULL AND TRIM(`SPACER BOX`) != '0') OR
                        (NULLIF(TRIM(`2 x 4 (TWO BY FOUR)`), '') IS NOT NULL AND TRIM(`2 x 4 (TWO BY FOUR)`) != '0') OR
                        (NULLIF(TRIM(`KUNA BOARD`), '') IS NOT NULL AND TRIM(`KUNA BOARD`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO BENCH MAT`), '') IS NOT NULL AND TRIM(`PEDI O POLE`) != '0') OR
                        (NULLIF(TRIM(`TRAVESSEIRO 1/2 LUA`), '') IS NOT NULL AND TRIM(`WALL UNIT CLÁSSICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CILINDRICO`), '') IS NOT NULL AND TRIM(`TRAV. CILINDRICO`) != '0') OR
                        (NULLIF(TRIM(`TRAV. OMBREIRA (PAR)`), '') IS NOT NULL AND TRIM(`TRAV. OMBREIRA (PAR)`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 30 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 30 mm`) != '0') OR
                        (NULLIF(TRIM(`TRAV. CABEC. 40 mm`), '') IS NOT NULL AND TRIM(`TRAV. CABEC. 40 mm`) != '0') OR
                        (NULLIF(TRIM(`CAPA PROT. BARREL CLÁSS.`), '') IS NOT NULL AND TRIM(`CAPA PROT. BARREL CLÁSS.`) != '0') OR
                        (NULLIF(TRIM(`SHEEPSKIN COVER`), '') IS NOT NULL AND TRIM(`SHEEPSKIN COVER`) != '0') OR
                        (NULLIF(TRIM(`BASTÃO ALUMÍNIO 1,5 M`), '') IS NOT NULL AND TRIM(`BASTÃO ALUMÍNIO 1,5 M`) != '0') OR
                        (NULLIF(TRIM(`PUXADOR DE ALUMINIO`), '') IS NOT NULL AND TRIM(`PUXADOR DE ALUMINIO`) != '0') OR
                        (NULLIF(TRIM(`ANEL DE PILATES ARCHIVE AÇO`), '') IS NOT NULL AND TRIM(`ANEL DE PILATES ARCHIVE AÇO`) != '0') OR
                        (NULLIF(TRIM(`MAGIC SQUARE`), '') IS NOT NULL AND TRIM(`MAGIC SQUARE`) != '0') OR
                        (NULLIF(TRIM(`FOOT CORREC. ALUM.`), '') IS NOT NULL AND TRIM(`FOOT CORREC. ALUM.`) != '0') OR
                        (NULLIF(TRIM(`BEAN BAG`), '') IS NOT NULL AND TRIM(`BEAN BAG`) != '0') OR
                        (NULLIF(TRIM(`BREATH A CIZER`), '') IS NOT NULL AND TRIM(`BREATH A CIZER`) != '0') OR
                        (NULLIF(TRIM(`NECK STRETCHER`), '') IS NOT NULL AND TRIM(`NECK STRETCHER`) != '0') OR
                        (NULLIF(TRIM(`HAND TENS O METER`), '') IS NOT NULL AND TRIM(`HAND TENS O METER`) != '0') OR
                        (NULLIF(TRIM(`TOE EXERCISER`), '') IS NOT NULL AND TRIM(`TOE EXERCISER`) != '0') OR
                        (NULLIF(TRIM(`AIR PLANE BOARD`), '') IS NOT NULL AND TRIM(`AIR PLANE BOARD`) != '0') OR
                        (NULLIF(TRIM(`PUSH UP DEVICE (PAR)`), '') IS NOT NULL AND TRIM(`PUSH UP DEVICE (PAR)`) != '0') OR
                        (NULLIF(TRIM(`MINI BARREL`), '') IS NOT NULL AND TRIM(`MINI BARREL`) != '0') OR
                        (NULLIF(TRIM(`MINI SPINE`), '') IS NOT NULL AND TRIM(`MINI SPINE`) != '0')
                    )
                    AND $condicaoPendente
                    ORDER BY STR_TO_DATE(`PRAZO DE PRODUCAO`, '%d/%m/%Y') ASC, `NUMERO PEDIDO` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge(self::ACESSORIOS_CLASSICO, self::ACESSORIOS_CLASSICO));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaControle()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, status_posvenda
                  FROM pedidos_prontos
                  WHERE status_posvenda NOT LIKE 'Finalizado'
                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostrarFilaExpedidos()
    {
        $query = "SELECT id, numero_pedido, prazo_producao, status_posvenda, data_conclusao
                  FROM pedidos_expedidos
                  WHERE status_posvenda LIKE 'Finalizado'
                  ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rastrearPedidos(?string $numeroBusca = null): array
    {
        $subItens = "
            SELECT numero_pedido,
                   MIN(prazo_producao) AS prazo_producao,
                   COUNT(*) AS total_itens,
                   SUM(status IN ('Embalado', 'Armazenado')) AS itens_embalados,
                   SUM(status = 'Armazenado') AS itens_armazenados
            FROM itens_producao
            WHERE equipamento NOT LIKE 'Emb.%'
            GROUP BY numero_pedido
            UNION ALL
            SELECT numero_pedido,
                   MIN(prazo_producao) AS prazo_producao,
                   COUNT(*) AS total_itens,
                   SUM(status IN ('Embalado', 'Armazenado')) AS itens_embalados,
                   SUM(status = 'Armazenado') AS itens_armazenados
            FROM itens_os
            WHERE equipamento NOT LIKE 'Emb.%'
            GROUP BY numero_pedido
        ";

        $query = "
            SELECT
                base.numero_pedido,
                pp.status_posvenda AS status_pipeline,
                COALESCE(pp.prazo_producao, prod.prazo_producao) AS prazo_pipeline,
                pe.status_posvenda AS status_finalizado,
                prod.total_itens,
                COALESCE(prod.itens_embalados, 0) AS itens_embalados,
                COALESCE(prod.itens_armazenados, 0) AS itens_armazenados
            FROM (
                SELECT numero_pedido FROM pedidos_prontos
                UNION
                SELECT numero_pedido FROM itens_producao
                UNION
                SELECT numero_pedido FROM itens_os
            ) base
            LEFT JOIN pedidos_prontos pp ON pp.numero_pedido = base.numero_pedido
            LEFT JOIN pedidos_expedidos pe ON pe.numero_pedido = base.numero_pedido
            LEFT JOIN ($subItens) prod ON prod.numero_pedido = base.numero_pedido
        ";

        $params = [];
        if ($numeroBusca !== null && trim($numeroBusca) !== '') {
            $query .= " WHERE base.numero_pedido LIKE ?";
            $params[] = '%' . trim($numeroBusca) . '%';
        } else {
            $query .= " WHERE NOT (
                            (COALESCE(pp.status_posvenda, '') = 'Expedido' OR COALESCE(pe.status_posvenda, '') = 'Finalizado')
                            AND COALESCE(prod.itens_armazenados, 0) >= COALESCE(prod.total_itens, 0)
                            AND COALESCE(prod.total_itens, 0) > 0
                        )";
        }

        $query .= " ORDER BY STR_TO_DATE(COALESCE(pp.prazo_producao, prod.prazo_producao, '01/01/2000'), '%d/%m/%Y') DESC, base.numero_pedido ASC";

        if ($numeroBusca === null || trim($numeroBusca) === '') {
            $query .= " LIMIT 300";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];
        foreach ($linhas as $l) {
            $totalItens = (int) ($l['total_itens'] ?? 0);
            $itensEmbalados = (int) ($l['itens_embalados'] ?? 0);
            $itensArmazenados = (int) ($l['itens_armazenados'] ?? 0);
            $statusPipeline = $l['status_pipeline'];
            $statusFinalizado = $l['status_finalizado'];

            $resultado[] = [
                'numero_pedido' => $l['numero_pedido'],
                'linha' => $this->linhaDoPedido($l['numero_pedido']),
                'prazo_producao' => $l['prazo_pipeline'],
                'producao' => [
                    'total_itens' => $totalItens,
                    'itens_embalados' => $itensEmbalados,
                    'concluida' => $totalItens > 0 && $itensEmbalados >= $totalItens,
                    'nao_iniciada' => $totalItens === 0,
                ],
                'armazenagem' => [
                    'total_itens' => $totalItens,
                    'itens_embalados' => $itensEmbalados,
                    'itens_armazenados' => $itensArmazenados,
                    // Concluída só quando TODO item do pedido (não só os que já
                    // chegaram a ser embalados) está Armazenado — senão um
                    // pedido com só os acessórios embalados aparecia como
                    // "armazenagem concluída" mesmo faltando os equipamentos
                    // principais nem terem sido produzidos ainda.
                    'concluida' => $totalItens > 0 && $itensArmazenados >= $totalItens,
                    'pendente' => $itensArmazenados < $totalItens,
                ],
                'financeiro' => $statusPipeline === 'Financeiro',
                'pos_venda' => $statusPipeline === 'Pós-venda',
                'expedicao_fila' => $statusPipeline === 'Expedicao',
                'finalizado' => $statusFinalizado === 'Finalizado' || $statusPipeline === 'Expedido',
            ];
        }

        return $resultado;
    }

public function mostrarFilaProducao(): array
{
    $query = "SELECT id, numero_pedido, prazo_producao, status AS status_item, 'NORMAL' AS origem
                FROM itens_producao 
                WHERE equipamento NOT LIKE '%Emb.%'
                
                UNION ALL
                
                SELECT id, numero_pedido, prazo_producao, status AS status_item, 'OS' AS origem
                FROM itens_os
                WHERE equipamento NOT LIKE '%Emb.%'
                
                ORDER BY STR_TO_DATE(prazo_producao, '%d/%m/%Y') ASC, numero_pedido ASC";

    try {
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $todosItens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pedidosAgrupados = [];
        $itensProcessadosPorPedido = [];

        foreach ($todosItens as $item) {
            $numPedido = $item['numero_pedido'];
            $idItem = $item['id'];
            $origem = $item['origem'];

            if (empty($numPedido)) {
                continue;
            }

            $chaveGrupo = $numPedido . '-' . $origem;

            if (!isset($itensProcessadosPorPedido[$chaveGrupo])) {
                $itensProcessadosPorPedido[$chaveGrupo] = [];
            }

            if (in_array($idItem, $itensProcessadosPorPedido[$chaveGrupo])) {
                continue;
            }

            $itensProcessadosPorPedido[$chaveGrupo][] = $idItem;

            if (!isset($pedidosAgrupados[$chaveGrupo])) {
                $pedidosAgrupados[$chaveGrupo] = [
                    'numero_pedido'    => $numPedido,
                    'origem'           => $origem,
                    'prazo_producao'   => $item['prazo_producao'],
                    'total_itens'      => 0,
                    'itens_concluidos'  => 0, 
                    'itens_armazenados' => 0  
                ];
            }

            $pedidosAgrupados[$chaveGrupo]['total_itens']++;

            $status = strtoupper(trim($item['status_item']));

            if ($status === 'ARMAZENADO' || $status === 'A') {
                $pedidosAgrupados[$chaveGrupo]['itens_armazenados']++;
            } elseif ($status === 'EMBALADO' || $status === 'E') {
                $pedidosAgrupados[$chaveGrupo]['itens_concluidos']++;
            }
        }

        return array_values($pedidosAgrupados);
    } catch (PDOException $e) {
        echo "Erro ao buscar fila de produção: " . $e->getMessage();
        return [];
    }
}
    public function pedidosMistos(string $tabela = 'itens_producao'): array
    {
        $tabela = ($tabela === 'itens_os') ? 'itens_os' : 'itens_producao';

        $equipamentosContemporaneo = self::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO;
        $equipamentosClassico = self::EQUIPAMENTOS_PRINCIPAIS_CLASSICO;

        $placeholdersContemporaneo = implode(',', array_fill(0, count($equipamentosContemporaneo), '?'));
        $placeholdersClassico = implode(',', array_fill(0, count($equipamentosClassico), '?'));

        $query = "SELECT DISTINCT a.numero_pedido
                  FROM $tabela a
                  WHERE a.equipamento IN ($placeholdersContemporaneo)
                    AND EXISTS (
                        SELECT 1 FROM $tabela b
                        WHERE b.numero_pedido = a.numero_pedido
                          AND b.equipamento IN ($placeholdersClassico)
                    )";

        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_merge($equipamentosContemporaneo, $equipamentosClassico));

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function linhaDoPedido(string $numeroPedido): string
    {
        $tabela = (stripos($numeroPedido, 'os') !== false) ? 'itens_os' : 'itens_producao';

        // 1ª checagem: equipamentos principais (mais confiável, nomes não se
        // repetem entre as linhas).
        $equipamentosContemporaneo = self::EQUIPAMENTOS_PRINCIPAIS_CONTEMPORANEO;
        $equipamentosClassico = self::EQUIPAMENTOS_PRINCIPAIS_CLASSICO;

        $temContemporaneo = $this->pedidoTemAlgumItem($tabela, $numeroPedido, $equipamentosContemporaneo);
        $temClassico = $this->pedidoTemAlgumItem($tabela, $numeroPedido, $equipamentosClassico);

        if ($temContemporaneo && $temClassico) {
            return 'Misto';
        }
        if ($temContemporaneo) {
            return 'Contemporâneo';
        }
        if ($temClassico) {
            return 'Clássico';
        }

        $acessoriosExclusivosContemporaneo = array_diff(self::ACESSORIOS_CONTEMPORANEO, self::ACESSORIOS_CLASSICO);
        $acessoriosExclusivosClassico = array_diff(self::ACESSORIOS_CLASSICO, self::ACESSORIOS_CONTEMPORANEO);
        $acessoriosCompartilhados = array_intersect(self::ACESSORIOS_CONTEMPORANEO, self::ACESSORIOS_CLASSICO);

        $temAcessorioContemporaneo = $this->pedidoTemAlgumItem($tabela, $numeroPedido, $acessoriosExclusivosContemporaneo);
        $temAcessorioClassico = $this->pedidoTemAlgumItem($tabela, $numeroPedido, $acessoriosExclusivosClassico);

        if ($temAcessorioContemporaneo && $temAcessorioClassico) {
            return 'Misto';
        }
        if ($temAcessorioContemporaneo) {
            return 'Contemporâneo';
        }
        if ($temAcessorioClassico) {
            return 'Clássico';
        }

        if ($this->pedidoTemAlgumItem($tabela, $numeroPedido, $acessoriosCompartilhados)) {
            return 'Indefinido (acessório comum)';
        }

        return 'Indefinido';
    }

    private function pedidoTemAlgumItem(string $tabela, string $numeroPedido, array $itens): bool
    {
        $itens = array_values($itens);
        if (empty($itens)) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($itens), '?'));
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tabela WHERE numero_pedido = ? AND equipamento IN ($placeholders)");
        $stmt->execute(array_merge([$numeroPedido], $itens));
        return (int) $stmt->fetchColumn() > 0;
    }

    public function mostrarPedidosReprogramados(array $filtros = []): array
    {
        $sql = "SELECT id, numero_pedido, prazo_producao, origem_tela, motivo, usuario_nome, criado_em
                FROM pedidos_reprogramados
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['pedido'])) {
            $sql .= " AND numero_pedido LIKE :pedido";
            $params[':pedido'] = '%' . $filtros['pedido'] . '%';
        }

        if (!empty($filtros['origem_tela'])) {
            $sql .= " AND origem_tela = :origem_tela";
            $params[':origem_tela'] = $filtros['origem_tela'];
        }

        if (!empty($filtros['data_ini'])) {
            $sql .= " AND DATE(criado_em) >= :data_ini";
            $params[':data_ini'] = $filtros['data_ini'];
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(criado_em) <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'];
        }

        $sql .= " ORDER BY criado_em DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as &$registro) {
            $registro['linha'] = $this->linhaDoPedido($registro['numero_pedido']);
        }
        unset($registro);

        if (!empty($filtros['linha'])) {
            $registros = array_values(array_filter($registros, function ($registro) use ($filtros) {
                return $registro['linha'] === $filtros['linha'];
            }));
        }

        return $registros;
    }
}
