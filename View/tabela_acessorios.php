<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessórios</title>
    <style>
            body { 
                font-family: 'Segoe UI', sans-serif; 
                background-color: #f4f7f6; 
                color: #333; 
                margin: 10px; 
                max-width: 100vw;
                overflow-x: hidden; 
            }

            .table-container {
                width: 100%;
                overflow-x: auto;
            }

            table { 
                width: 100%; 
                table-layout: fixed; 
                border-collapse: collapse; 
                background: #fff; 
                box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
                border-radius: 8px; 
                overflow: hidden; 
                border: 1px solid black;
                border-radius: 20px;
            }

            th { 
                background-color: #ffffff;
                height: 50px;
                color: black; 
                padding: 10px 5px; 
                text-transform: uppercase; 
                font-size: 18px; 
                word-wrap: break-word; 
            }

            td { 
                padding: 8px 4px; 
                border: 1px solid #1c1c1c; 
                text-align: center; 
                font-size: 22px; 
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap; 
            }

            td:first-child, th:first-child { 
                font-weight: bold; 
                color: blue; 
                width: 60px; 
                font-size: 16px;
            } 

            .column-data{
                font-weight: bold;
                font-size: 20px;
                color: #bb4242;
            }

            .qr-link { 
                background: #2ecc71; 
                color: white; 
                padding: 2px 4px; 
                border-radius: 4px; 
                font-size: 10px; 
                text-decoration: none; 
                font-weight: bold; 
                display: block; 
                margin: 2px 0;
            }

           
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

            #flash-effect { 
                position: fixed; 
                top: 0; 
                left: 0; 
                width: 100vw; 
                height: 100vh; 
                background: rgba(46, 204, 113, 0.6);
                opacity: 0; 
                pointer-events: none; 
                z-index: 9999; 
            }
            
            .flash-active { 
                animation: pulseFlash 0.2s ease-out; 
            }

            @keyframes pulseFlash {
                0% { opacity: 0; }
                50% { opacity: 1; }
                100% { opacity: 0; }
            }
    </style>
</head>
<body>
    <input type="text" id="input-pistola" autofocus>
    <table>
        <?php
        require_once '../config/Database.php'; 
        require_once '../Model/Sistema.php'; 

        $database = new Database();
        $db = $database->getConnection();

        $sistema = new Sistema($db);

        $pedidos = $sistema->mostrarTabelaAcessorios(); 
        ?>
        <?php
        if (!isset($pedidos)) {
            $pedidos = [];
        }

        $pedidos_agrupados = $pedidos; 
        ?>
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Prazo</th>
                <th>Caixa Mini</th>
                <th>Caixa do Reformer</th>
                <th>P. de Molas - B</th>
                <th>P. de Molas - C</th>
                <th>P. de Molas - PT</th>
                <th>Caixa da Cadeira</th>
                <th>Prancha de Alongamento</th>
            </tr>
        </thead>
        <tbody>
    <?php 
    $equipamentos = [ 
        'Caixa Mini', 
        'Caixa do Reformer', 
        'P. de Molas - B R I N D E',
        'P. de Molas - C O M P L E T A', 
        'P. de Molas - P u s h T h r u',
        'Caixa da Cadeira', 
        'Prancha de Alongamento',
    ];

    $database = new Database();
    $db = $database->getConnection();

    foreach ($pedidos_agrupados as $pedido): ?>
    <tr>
        <td class="num-column"><?= htmlspecialchars($pedido['numero'])?></td>
        
        <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 10)) ?></td>
        
        <?php foreach ($equipamentos as $nome_equipamento): 
            $stmt = $db->prepare("SELECT id, status FROM itens_producao WHERE numero_pedido = ? AND equipamento = ? AND numero_pedido");
            $stmt->execute([$pedido['numero'], $nome_equipamento]);
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        ?>
            <td>
                <div style="display: flex; justify-content: center;">
                <?php if ($pecas && count($pecas) > 0): 
                    foreach ($pecas as $peca):
                        $status = isset($peca['status']) ? $peca['status'] : 'Pendente';
                        $id_peca = isset($peca['id']) ? $peca['id'] : 0;
                        
                        $texto = '❌';
                        $estilo = '';

                    if ($peca['status'] === 'Finalizado') {
                        $texto = '✅';
                    } elseif ($peca['status'] === 'Embalado') {
                        $texto = 'E';
                        $estilo = 'style="color: #27ae60; font-weight: bold; font-size: 30px;"';
                    }
                ?>
                    <span class="item-check" 
                        data-id="<?= $peca['id'] ?>" 
                        <?= $estilo ?> 
                        style="font-size: 25px;">
                        <?= $texto ?>
                    </span>
                    <?php endforeach;?>
                <?php else: ?>
                    <span style="color: #ccc;">-</span>
                <?php endif; ?>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</tbody>
    </table>

    <div id="flash-effect"></div>


    <script>
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tempoRefresh = urlParams.get('refresh') || null; 
            if (tempoRefresh) {
                setTimeout(() => {
                    window.location.href = `index.php?refresh=${tempoRefresh}`;
                }, tempoRefresh * 1000);
            }
        })();

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

        function atualizarStatusNoBanco(codigoCompleto) {
            fetch(`../atualizar_etapa.php?id=${codigoCompleto}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = document.querySelector(`.item-check[data-id="${data.idReal}"]`);
                        
                        if (icon) {
                            if (data.statusGerado === 'Finalizado') {
                                icon.innerText = '✅';
                                icon.style.color = ''; 
                                icon.style.fontWeight = 'normal';
                            } else if (data.statusGerado === 'Embalado') {
                                icon.innerText = 'E';
                                icon.style.color = '#27ae60'; 
                                icon.style.fontWeight = 'bold';
                                icon.style.fontSize = '30px';
                            }
                            
                            dispararFeedbackCerto(); 
                            verificarLinha(icon.closest('tr'));
                        }
                    } else {
                        console.error("Erro no servidor:", data.error);
                    }
                })
                .catch(err => console.error("Erro na requisição:", err));
        }

        function verificarLinha(linha) {
            const itensLista = linha.querySelectorAll('.item-check');
            
            if (itensLista.length === 0) return;
           
            const pendentes = Array.from(itensLista).filter(function(i) {
                return i.innerText.trim() !== 'E';
            });

            if (pendentes.length === 0) {
                const numeroPedido = linha.cells[0].innerText.trim();

                fetch(`../notificar_posVenda.php?pedido=${encodeURIComponent(numeroPedido)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log(`Sucesso: Pedido ${numeroPedido} enviado para a fila do Pós-Venda.`);
                        }
                    })
                    .catch(err => console.error("Falha na comunicação com o servidor:", err));

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

        let scrollSpeed = 0; 
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