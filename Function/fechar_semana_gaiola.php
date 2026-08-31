<?php
/*
 * Fecha a semana atual da Gaiola Cadilac (planejado - real, somando
 * Contemporâneo + Clássico + Clássico Tauari — ver
 * Sistema::EQUIPAMENTOS_GAIOLA_CADILAC) e grava o saldo em
 * gaiola_atrasos_semanais, pra alimentar o contador "Atrasados" nas telas
 * de produção. Feito pra rodar via CRON toda semana (sábado) — CLI
 * apenas, não é acessível por URL/navegador.
 *
 * Configuração no painel da Hostinger (hPanel > Avançado > Cron Jobs):
 *   Frequência: toda semana, sábado, no horário desejado (ex: 23:00)
 *   Comando: php /home/SEU_USUARIO/public_html/Function/fechar_semana_gaiola.php
 * (ajuste o caminho pro caminho real do projeto no servidor)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso permitido apenas via linha de comando (cron).');
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Model/Sistema.php';

$database = new Database();
$db = $database->getConnection();
$sistema = new Sistema($db);

foreach (['itens_producao', 'itens_os'] as $tabela) {
    $resultado = $sistema->fecharSemanaGaiolaCadilac($tabela);
    echo sprintf(
        "[%s] %s: semana %s a %s — planejado=%d real=%d deficit=%d\n",
        date('Y-m-d H:i:s'),
        $tabela,
        $resultado['semana_inicio'],
        $resultado['semana_fim'],
        $resultado['planejado'],
        $resultado['real'],
        $resultado['deficit']
    );

}
