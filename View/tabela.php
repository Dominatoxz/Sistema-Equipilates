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
        td { padding: 12px; border: 1px solid #1c1c1c; text-align: center; font-size: 18px; }
        td:first-child { font-weight: bold; color: #db8534; width: 100px; }
        .item-check { cursor: pointer; user-select: none; transition: transform 0.1s; display: inline-block; }
        .item-check:active { transform: scale(1.2); }
        .qr-link { background: #2ecc71; color: white; padding: 3px 6px; border-radius: 4px; font-size: 11px; text-decoration: none; font-weight: bold; }
        #flash-effect { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(46, 204, 113, 0.3); pointer-events: none; opacity: 0; z-index: 9999; }
        .flash-active { opacity: 1 !important; transition: opacity 0.1s; }
        input#input-pistola {
                position: fixed; 
                top: 0;
                left: 0;
                width: 0;
                height: 0;
                opacity: 0;
                border: none;
                padding: 0;
                margin: 0;
                pointer-events: none; 
                z-index: -1;          
            }
    </style>
</head>
<body>
    <input type="text" id="input-pistola" autofocus>
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
                <th>Carrinho (Ref)</th><th>CR Code</th>
                <th>Torre</th><th>RT Code</th>
                <th>Carrinho (Tor)</th><th>CT Code</th>
                <th>Cadilac</th><th>CD Code</th>
                <th>Gaiola</th><th>G Code</th>
                <th>Chair</th><th>SC Code</th>
                <th>Barrel</th><th>B Code</th>
            </tr>
        </thead>
        <tbody>
    <?php 
    $equipamentos = [
        'Reformer Excellence', 
        'Carrinho Excellence', 
        'Reformer Torre', 
        'Carrinho Torre',
        'Cadilac Excelence', 
        'Gaiola Cadilac',
        'Step Chair Excelence', 
        'Lader Barrel Excelence',
    ];

    $database = new Database();
    $db = $database->getConnection();

    foreach ($pedidos_agrupados as $pedido): ?>
    <tr>
        <td><?= htmlspecialchars($pedido['numero']) ?></td>
        
        <?php foreach ($equipamentos as $nome_equipamento): 
            $stmt = $db->prepare("SELECT id, status FROM itens_producao WHERE numero_pedido = ? AND equipamento = ?");
            $stmt->execute([$pedido['numero'], $nome_equipamento]);
            $itens_no_banco = $stmt->fetchAll(PDO::FETCH_ASSOC)
        ?>
            <td>
                <div style="display: flex; justify-content: center; gap: 5px;">
                    <?php if ($itens_no_banco): ?>
                        <?php foreach ($itens_no_banco as $peca): 
                            $texto = '❌';
                            $estilo = '';

                            if ($peca['status'] === 'Finalizado') {
                                $texto = '✅';
                            } elseif ($peca['status'] === 'Embalado') {
                                $texto = 'E';
                                $estilo = 'style="color: #27ae60; font-weight: bold;"';
                            }
                        ?>
                            <span class="item-check" 
                                data-id="<?= $peca['id'] ?>" 
                                <?= $estilo ?> 
                                style="font-size: 16px;">
                                <?= $texto ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #ccc;">-</span>
                    <?php endif; ?>
                </div>
            </td>
            <td>
                <?php if (count($itens_no_banco) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 3px; align-items: center;">
                        <?php foreach ($itens_no_banco as $index => $peca): ?>
                            <a href="gerar_qr.php?numero=<?= urlencode($pedido['numero']) ?>&item=<?= urlencode($nome_equipamento) ?>&pos=<?= $index + 1 ?>" 
                               target="_blank" class="qr-link">Code <?= $index + 1 ?></a>
                        <?php endforeach; ?>
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
        const inputPistola = document.getElementById('input-pistola');

        document.addEventListener('click', () => inputPistola.focus({preventScroll: true}));
        window.onload = () => inputPistola.focus({preventScroll: true});

        inputPistola.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { 
                const idLido = this.value;
                this.value = ''; 

                if (idLido) {
                    atualizarStatusNoBanco(idLido);
                }
            }
        });

    function atualizarStatusNoBanco(id) {
        fetch(`atualizar_etapa.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = document.querySelector(`.item-check[data-id="${id}"]`);
                    if (icon) {
                        if (data.novoStatus === 'Finalizado') {
                            icon.innerText = '✅';
                            dispararFeedbackCerto();
                        } else if (data.novoStatus === 'Embalado') {
                            icon.innerText = 'E';
                            icon.style.color = '#27ae60';
                            icon.style.fontWeight = 'bold';
                            icon.style.fontFamily = 'Arial';
                            
                            dispararFeedbackCerto();
                            verificarLinha(icon.closest('tr'));
                            
                        }

                    
                    } 
                }
            })
            .catch(err => console.error("Erro ao processar leitura:", err));
}

        function verificarLinha(linha) {
            const itens = linha.querySelectorAll('.item-check');
            const pendentes = Array.from(itens).filter(i => i.innerText !== 'E');
            if (pendentes.length === 0) {
                linha.style.transition = "opacity 0.8s";
                linha.style.background = "#d4edda";
                setTimeout(() => linha.remove(), 1000);
            }
        }

        function dispararFeedbackCerto() {
            const flash = document.getElementById('flash-effect');
            flash.classList.add('flash-active');
            setTimeout(() => flash.classList.remove('flash-active'), 150);
            new Audio('../audios/som-sucesso.mp3').play().catch(() => {});
        }

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