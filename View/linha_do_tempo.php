<?php
require_once '../Function/trava.php';
verificarAcessoSetor(CARGOS_LINHA_DO_TEMPO);
require_once '../config/Database.php';
require_once '../Function/historico_quadro.php';
date_default_timezone_set('America/Sao_Paulo');

$db = (new Database())->getConnection();
$inicio = null;
$atividade = [];
$erro = null;
try {
    $inicio = hqInicioRegistro($db);
    if ($inicio) {
        $q = $db->query("SELECT DATE_FORMAT(CONVERT_TZ(ocorrido_utc, '+00:00', '-03:00'), '%Y-%m-%d %H') h, COUNT(*) q
                         FROM historico_quadro WHERE operacao <> 'S'
                           AND ocorrido_utc >= UTC_TIMESTAMP() - INTERVAL 72 HOUR
                         GROUP BY h");
        foreach ($q->fetchAll(PDO::FETCH_KEY_PAIR) as $h => $n) $atividade[$h] = (int) $n;
    }
} catch (Throwable $e) {
    $erro = 'O registro de histórico ainda não foi instalado neste banco.';
    error_log('linha_do_tempo: ' . $e->getMessage());
}
$agora = new DateTime('now');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linha do tempo do quadro</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f1f5f9; color: #0f172a; height: 100vh; display: flex; flex-direction: column; }
        header { background: linear-gradient(120deg, #0f172a, #1e3a8a); color: #fff; padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        header h1 { font-size: 18px; margin: 0 14px 0 0; }
        .ctl { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        button, input[type=datetime-local] { font: inherit; border: 0; border-radius: 8px; padding: 7px 11px; }
        button { background: rgba(255, 255, 255, .16); color: #fff; cursor: pointer; font-weight: 600; }
        button:hover { background: rgba(255, 255, 255, .3); }
        button.ativo { background: #22c55e; }
        input[type=datetime-local] { background: #fff; color: #0f172a; }
        .faixa { padding: 8px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .faixa small { color: #64748b; }
        .horas { display: flex; gap: 2px; overflow-x: auto; padding: 6px 0 2px; align-items: flex-end; height: 62px; }
        .h { flex: 0 0 22px; text-align: center; cursor: pointer; font-size: 9px; color: #64748b; }
        .h .b { background: #cbd5e1; border-radius: 3px 3px 0 0; min-height: 3px; width: 100%; }
        .h.tem .b { background: #3b82f6; }
        .h.sel .b { background: #f59e0b; }
        .h:hover .b { background: #1d4ed8; }
        .h.dia { font-weight: 800; color: #0f172a; }
        a.voltar { background: rgba(255, 255, 255, .16); color: #fff; text-decoration: none; font-weight: 600; border-radius: 8px; padding: 7px 12px; }
        a.voltar:hover { background: rgba(255, 255, 255, .3); }
        iframe { flex: 1; width: 100%; border: 0; background: #f4f7f6; }
        .aviso { padding: 20px; color: #b91c1c; font-weight: 600; }
    </style>
</head>

<body>
    <header>
        <a class="voltar" href="../index.php">← Central</a>
        <h1>🕒 Linha do tempo do quadro</h1>
        <div class="ctl">
            <button data-h="-3">−3h</button>
            <button data-h="-1">−1h</button>
            <input type="datetime-local" id="momento" step="60">
            <button data-h="1">+1h</button>
            <button data-h="3">+3h</button>
            <button id="agora">Agora</button>
        </div>
        <div class="ctl" id="atalhos"></div>
    </header>

    <?php if ($erro): ?>
        <div class="aviso"><?= htmlspecialchars($erro) ?></div>
    <?php else: ?>
        <div class="faixa">
            <div class="horas" id="horas"></div>
        </div>
        <iframe id="quadro" title="Quadro Contemporâneo no momento escolhido"></iframe>
    <?php endif; ?>

    <script>
        const inicio = <?= $inicio ? json_encode($inicio->format('Y-m-d\TH:i')) : 'null' ?>;
        const agora = new Date(<?= json_encode($agora->format('Y-m-d\TH:i:s')) ?>);
        const atividade = <?= json_encode($atividade) ?>;
        const inp = document.getElementById('momento');
        const quadro = document.getElementById('quadro');
        if (!quadro) throw new Error('sem registro');

        const pad = n => String(n).padStart(2, '0');
        const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

        function ir(d) {
            let v = fmt(d);
            if (inicio && v < inicio) v = inicio;
            if (v > fmt(agora)) v = fmt(agora);
            inp.value = v;
            inp.min = inicio || '';
            inp.max = fmt(agora);
            quadro.src = 'Contemporaneo/tabela.php?t=' + encodeURIComponent(v.replace('T', ' ') + ':00');
            document.querySelectorAll('.h').forEach(e => e.classList.toggle('sel', e.dataset.k === v.slice(0, 13)));
        }

        // barras por hora (72h)
        const caixa = document.getElementById('horas');
        const max = Math.max(1, ...Object.values(atividade));
        for (let i = 72; i >= 0; i--) {
            const d = new Date(agora.getTime() - i * 3600e3);
            const k = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}`;
            const n = atividade[k] || 0;
            const el = document.createElement('div');
            el.className = 'h' + (n ? ' tem' : '') + (d.getHours() === 0 ? ' dia' : '');
            el.dataset.k = k.replace(' ', 'T');
            el.title = `${pad(d.getDate())}/${pad(d.getMonth() + 1)} ${pad(d.getHours())}h — ${n} mudança(s)`;
            el.innerHTML = `<div class="b" style="height:${3 + (n / max) * 38}px"></div>${d.getHours() === 0 ? pad(d.getDate()) + '/' + pad(d.getMonth() + 1) : pad(d.getHours())}`;
            el.onclick = () => ir(new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), 0, 0));
            caixa.appendChild(el);
        }

        document.querySelectorAll('button[data-h]').forEach(b => b.onclick = () => {
            const cur = new Date(inp.value || fmt(agora));
            ir(new Date(cur.getTime() + Number(b.dataset.h) * 3600e3));
        });
        document.getElementById('agora').onclick = () => ir(agora);
        inp.onchange = () => ir(new Date(inp.value));

        // atalhos: ontem 18:00 / 18:30 / 18:59 e início do dia de hoje
        const at = document.getElementById('atalhos');
        [['Ontem 18:30', -1, 18, 30], ['Ontem 18:59', -1, 18, 59], ['Hoje 08:00', 0, 8, 0]].forEach(([t, dd, h, m]) => {
            const b = document.createElement('button');
            b.textContent = t;
            b.onclick = () => ir(new Date(agora.getFullYear(), agora.getMonth(), agora.getDate() + dd, h, m, 0));
            at.appendChild(b);
        });

        ir(agora);
    </script>
</body>

</html>
