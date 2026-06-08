<?php
require_once '../Function/trava.php'; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itens</title>
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

            .sem-pedidos { 
                text-align: center; 
                padding: 50px; 
                color: #7f8c8d; 
                font-size: 18px; 
                font-weight: 500; 
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
                font-size: 15px; 
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
            .footer {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
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

        $pedidos = $sistema->mostrarTabelaClassicoOs(); 
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
                <th>Reformer Alumin</th>
                <th>Carrinho (A)</th>
                <th>Reformer Torre</th>
                <th>Carrinho (Tor)</th>
                <th>Cadilac Alumin</th>
                <th>Gaiola (A)</th>
                <th>Reformer Tauari</th>
                <th>Carrinho (T)</th>
                <th>Cadilac Tauari</th>
                <th>Gaiola (T)</th>
                <th>Acessórios</th>
            </tr>   
        </thead>
        <tbody>
    <?php 
    $equipamentos = [
        'REF. CLASSICO ALUMINIO', 
        'CARRINHO CLASSICO',
        'REF. CLASSICO TORRE', 
        'CARRINHO CLASSICO TORRE',
        'CAD. CLASSICO ALUMINIO', 
        'GAIOLA CLASSICO',
        'REF. CLASSICO TAUARI',
        'CARRINHO CLASSICO TAUARI',
        'CAD. CLASSICO TAUARI', 
        'GAIOLA CADILCAC TAUARI'
    ];

    $lista_acessorios = [ 
        'CAIXA DO REFORMER CLÁSSICA', 
        'SPINE CORRECTOR',
        'SMALL BARREL', 
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
        'MINI SPINE',
        'TRAV. CILINDRICO',
        'TRAV. OMBREIRA (PAR)',
        'TRAV. CABEC. 30 mm',
        'TRAV. CABEC. 40 mm',
        'CAPA PROT. BARREL CLÁSS.',
        'SHEEPSKIN COVER',
        'BASTÃO ALUMÍNIO 1,5 M',
        'PUXADOR DE ALUMINIO',
        'ANEL DE PILATES ARCHIVE AÇO',
        'MAGIC SQUARE'
    ];

    $placeholders_acessorios = implode(',', array_fill(0, count($lista_acessorios), '?'));

    $database = new Database();
    $db = $database->getConnection();?>

    <?php if (empty($pedidos)): ?>
            <tr>
                <td colspan="12" class="sem-pedidos">Nenhum item em produção pendente na fábrica.</td>
            </tr>
    <?php else: ?>

    <?php foreach ($pedidos_agrupados as $pedido): ?>
    <tr>
        <td><?= htmlspecialchars($pedido['numero'])?></td>
        
        <td class="column-data"><?= htmlspecialchars(substr($pedido['prazo_producao'], 0, 10)) ?></td>

        <?php foreach ($equipamentos as $nome_equipamento): 
            $stmt = $db->prepare("SELECT id, status FROM itens_os WHERE numero_pedido = ? AND equipamento = ? AND numero_pedido LIKE 'OS%'");
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
        <td>
            <?php
            $sqlAcess = "SELECT status FROM itens_os WHERE numero_pedido = ? AND equipamento IN ($placeholders_acessorios)";
            $stmtAcess = $db->prepare($sqlAcess);
            $paramsAcess = array_merge([$pedido['numero']], $lista_acessorios);
            $stmtAcess->execute($paramsAcess);
            $status_acessorios = $stmtAcess->fetchAll(PDO::FETCH_COLUMN);

            if (count($status_acessorios) === 0) {
                echo '<span style="color: #ccc; font-size: 20px;">-</span>';
            } else {
                $totalAcess = count($status_acessorios);
                $totalEmbalados = 0;
                $totalFinalizados = 0;

                foreach ($status_acessorios as $st) {
                    if ($st === 'Embalado') $totalEmbalados++;
                    if ($st === 'Finalizado') $totalFinalizados++;
                }

                if ($totalEmbalados === $totalAcess) {
                    echo '<span class="item-check status-acessorio-coletivo" style="color: #27ae60; font-weight: bold; font-size: 30px;">E</span>';
                } elseif (($totalEmbalados + $totalFinalizados) === $totalAcess) {
                    echo '<span class="item-check status-acessorio-coletivo" style="font-size: 25px;">✅</span>';
                } else {
                    echo '<span class="item-check status-acessorio-coletivo" style="font-size: 25px;">❌</span>';
                }
            }
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</tbody>
    </table>

    <div id="flash-effect"></div>


    <script>
        let pedidosAtuais = Array.from(document.querySelectorAll('tbody tr[id^="linha-"]'))
            .map(tr => tr.id.replace('linha-', ''));

        function verificarAtualizacoesRapidas() {
            fetch('../Function/dados_tabelas.php?tela=producao')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosDados = data.dados;
                        
                        const novosPedidos = novosDados.map(p => {
                        const valorPedido = p.numero_pedido || p.numero || '';
                        return valorPedido.toString();
                    });

                        
                        const temNovoItem = novosPedidos.some(num => !pedidosAtuais.includes(num));
                        const itemSumiu = pedidosAtuais.some(num => !novosPedidos.includes(num));

                        if (temNovoItem || itemSumiu) {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => console.error("Erro na sincronização rápida:", err));
        }

        setInterval(verificarAtualizacoesRapidas, 0);

        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tempoRefresh = urlParams.get('refresh') || null; 
            if (tempoRefresh) {
                setTimeout(() => {
                    window.location.href = `index.php?&refresh=${tempoRefresh}`;
                }, tempoRefresh * 500);
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
    fetch(`../Function/atualizar_etapa.php?id=${codigoCompleto}`)
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
                    setTimeout(() => {
                        window.location.reload()
                    }, 200)
                } else{
                    dispararFeedbackCerto(); 
                    window.location.reload();
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
   
    const pendentes = Array.from(itensLista).filter(i => i.innerText.trim() !== 'E');

    if (pendentes.length === 0) {
        const numeroPedido = linha.cells[0].innerText.trim();

        fetch(`../Function/notificar_posVenda_classico.php?pedido=${encodeURIComponent(numeroPedido)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    if (data.status_pedido === 'SUBIU_POS_VENDA') {
                        linha.style.transition = "opacity 0.8s, background 0.5s";
                        linha.style.background = "#d4edda";
                        setTimeout(() => linha.remove(), 1000);
                    } else {
                        linha.style.transition = "background 0.5s";
                        linha.style.background = "#ffeaa7"; 
                    }
                } else {
                    console.error("O banco recusou a inserção:", data.error);
                }
            })
            .catch(err => console.error("Falha na comunicação com o servidor:", err));
    }
}

        document.querySelectorAll('tbody tr').forEach(tr => verificarLinha(tr));

        function dispararFeedbackCerto() {
            const flash = document.getElementById('flash-effect');
            flash.classList.add('flash-active');
            setTimeout(() => flash.classList.remove('flash-active'), 150);;
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
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>
</html>