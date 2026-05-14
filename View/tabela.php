<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadro de Produção</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; color: #333; margin: 20px; }
        h1 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th { background-color: #db8534; color: white; padding: 15px; text-transform: uppercase; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; font-size: 18px; }
        td:first-child { font-weight: bold; color: #db8534; width: 100px; }
        .item-check { cursor: pointer; user-select: none; transition: transform 0.1s; display: inline-block; }
        .item-check:active { transform: scale(1.2); }
        .qr-link { background: #2ecc71; color: white; padding: 3px 6px; border-radius: 4px; font-size: 11px; text-decoration: none; font-weight: bold; }
        #flash-effect { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(46, 204, 113, 0.3); pointer-events: none; opacity: 0; z-index: 9999; }
        .flash-active { opacity: 1 !important; transition: opacity 0.1s; }
    </style>
</head>
<body>
    <table>
        <?php
        if (!isset($pedidos)) {
            $pedidos = [];
        }

        $pedidos_agrupados = $pedidos; 
        ?>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Reformer</th><th>R Code</th>
                <th>Torre</th><th>RT Code</th>
                <th>Cadilac</th><th>CD Code</th>
                <th>Chair</th><th>SC Code</th>
                <th>Barrel</th><th>B Code</th>
                <th>Carrinho</th><th>C Code</th>
                <th>Gaiola</th><th>G Code</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $equipamentos = [
                'Reformer Excellence', 'Reformer Torre', 'Cadilac Excelence', 
                'Step Chair Excelence', 'Lader Barrel Excelence', 'Carrinho', 'Gaiola'
            ];

            foreach ($pedidos_agrupados as $pedido): ?>
            <tr>
                <td><?= htmlspecialchars($pedido['numero']) ?></td>
                
                <?php foreach ($equipamentos as $nome): 
                    $qtd = isset($pedido[$nome]) ? (int)$pedido[$nome] : 0;
                ?>
                    <td style="min-width: 80px;">      
                        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 4px;">
                            <?php if ($qtd > 0): ?>
                                <?php for ($i = 1; $i <= $qtd; $i++): ?>
                                    <span class="item-check">❌</span>
                                <?php endfor; ?>
                            <?php else: ?>
                                <span style="color:#ccc; font-size: 14px;">-</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td>
                        <?php if ($qtd > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 3px; align-items: center;">
                                <?php for ($i = 1; $i <= $qtd; $i++): ?>
                                    <a href="gerar_qr.php?numero=<?= urlencode($pedido['numero']) ?>&item=<?= urlencode($nome) ?>&pos=<?= $i ?>" 
                                       target="_blank" class="qr-link">QR <?= $i ?></a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="flash-effect"></div>

    <script>
        document.querySelectorAll('.item-check').forEach(item => {
            item.addEventListener('click', function() {
                if (this.innerText === '❌') {
                    this.innerText = '✅';
                    dispararFeedback();
                } else if (this.innerText === '✅') {
                    this.innerText = 'E';
                    this.style.color = '#27ae60';
                    this.style.fontWeight = 'bold';
                    this.style.fontFamily = 'Arial';
                    dispararFeedback();
                    verificarLinha(this.closest('tr'));
                } else {
                    this.innerText = '❌';
                    this.style.color = '#333';
                }
            });
        });

        function verificarLinha(linha) {
            const itens = linha.querySelectorAll('.item-check');
            const pendentes = Array.from(itens).filter(i => i.innerText !== 'E');
            if (pendentes.length === 0) {
                linha.style.transition = "opacity 0.8s";
                linha.style.background = "#d4edda";
                setTimeout(() => linha.remove(), 1000);
            }
        }

        function dispararFeedback() {
            const flash = document.getElementById('flash-effect');
            flash.classList.add('flash-active');
            setTimeout(() => flash.classList.remove('flash-active'), 150);
            new Audio('../audios/som-sucesso.mp3').play().catch(() => {});
        }

        // Scroll Automático
        let scrollSpeed = 0.5; 
        function autoScroll() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                setTimeout(() => window.scrollTo(0, 0), 3000);
            } else {
                window.scrollBy(0, scrollSpeed);
            }
            requestAnimationFrame(autoScroll);
        }
        window.onload = () => { if(scrollSpeed > 0) autoScroll(); };
    </script>
</body>
</html>