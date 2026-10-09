<?php
/*
 * Ponte ERP/app -> bipagem. Vitor, 2026-10-07: "bipe de etiquetas que podem ser no
 * escanear para apontar produção, embalagem, qualidade, armazenamento" (app mobile do ERP).
 *
 * NÃO duplica regra de negócio: preenche uma sessão "de serviço" e executa o próprio
 * atualizar_etapa.php (máquina de estados P → Produzido, E → Embalado, E na expedição →
 * Armazenado, gate de qualidade, notificarPosProducao, limpeza do cache). Qualquer mudança
 * futura nele vale automaticamente aqui.
 *
 * Quem decide QUEM pode bipar é o ERP (api/bipar.php, mesmos cargos do site, via
 * profiles.apontamento_usuario). Este arquivo só aceita chamadas com o token do ERP
 * (ERP_APONTAR_TOKEN no .env, header X-Erp-Token) e registra no error_log quem bipou.
 *
 * POST JSON: { codigo: "123-P" | "OS45-E", origem: "producao"|"expedicao", usuario: "nome" }
 */
require_once '../global.php';
header('Content-Type: application/json');
ini_set('display_errors', '0'); // aviso de ini_set da sessão (trava.php) não pode sujar o JSON

$tokenEsperado = getenv('ERP_APONTAR_TOKEN');
$tokenRecebido = $_SERVER['HTTP_X_ERP_TOKEN'] ?? '';
if (empty($tokenEsperado) || !hash_equals($tokenEsperado, $tokenRecebido)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Use POST.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$codigo = is_array($body) ? trim((string)($body['codigo'] ?? '')) : '';
$origem = (is_array($body) && ($body['origem'] ?? '') === 'expedicao') ? 'expedicao' : 'producao';
$usuario = is_array($body) ? substr(preg_replace('/[^\p{L}\p{N} ._@-]/u', '', (string)($body['usuario'] ?? 'app')), 0, 60) : 'app';
if ($codigo === '' || !preg_match('/^[A-Za-z0-9-]{1,40}$/', $codigo)) {
    echo json_encode(['success' => false, 'error' => 'Código de etiqueta inválido.']);
    exit;
}

// Sessão de serviço: mesmas configurações do trava.php, sem cookie e descartada no fim.
ini_set('session.use_cookies', '0');
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
session_id('erpapp' . bin2hex(random_bytes(12)));
session_start();
$_SESSION['usuario_id'] = 0;
$_SESSION['usuario_logado'] = 'erp:' . $usuario;
$_SESSION['nivel_acesso'] = 'ERP';
register_shutdown_function(function () {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
    }
});

error_log('[apontar_erp] ' . $usuario . ' bipou ' . $codigo . ' origem=' . $origem);

$_GET['id'] = $codigo;
$_GET['origem'] = $origem;
require __DIR__ . '/atualizar_etapa.php';
