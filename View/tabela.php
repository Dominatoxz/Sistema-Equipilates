<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadro</title>
</head>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7f6;
    color: #333;
}

h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
}

table {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    border-collapse: collapse; 
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
    border-radius: 8px;
    overflow: hidden; 
}

th {
    background-color: #db8534;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 15px;
    text-align: left;
}

td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #eee;
    text-align: center;
}
thead th {
    text-align: center;
}

tbody td{
    font-size: 25px;
}

a {
    text-decoration: none;
    color: inherit;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}


tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
}


td:first-child {
    font-weight: bold;
    color: #db8534;
    width: 80px;
}

#flash-effect {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(46, 204, 113, 0.4);
    pointer-events: none; 
    opacity: 0;
    transition: opacity 0.1s ease-out;
    z-index: 9999;
}

.flash-active {
    opacity: 1 !important;
}

</style>
<body>
    <table border="1">
<?php
$pedidos_agrupados = [];
if (!isset($pedidos)) {
    $pedidos = [];
}
foreach ($pedidos as $linha) {
    $num = $linha['numero_pedido'];
    $item = $linha['item'];
    $qtd = $linha['quantidade'];
    
    if (!isset($pedidos_agrupados[$num])) {
        $pedidos_agrupados[$num] = [
            'numero' => $num,
            'Reformer' => 0,
            'Reformer Torre' => 0,
            'Cadilac' => 0,
            'Step Chair' => 0,
            'Barrel' => 0,
            'Carrinho' => 0,
            'Gaiola' => 0
        ];
    }
    $pedidos_agrupados[$num][$item] = $qtd;
}
?>
    <thead>
        <tr>
            <th>Número do Pedido</th>
            <th>Reformer</th>
            <th>Reformer Torre</th>
            <th>Cadilac</th>
            <th>Step Chair</th>
            <th>Barrel</th>
            <th>Carrinho</th>
            <th>Gaiola</th>
        </tr>
    </thead>
        <tbody>
            <?php foreach ($pedidos_agrupados as $pedido): ?>
            <tr>
                <td><?= $pedido['numero'] ?></td>
                
                <?php 
                $equipamentos = ['Reformer', 'Reformer Torre', 'Cadilac', 'Step Chair', 'Barrel', 'Carrinho', 'Gaiola'];
                
                foreach ($equipamentos as $nome): 
                    $qtd = $pedido[$nome];
                ?>
                    <td>
                        <?php if ($qtd > 0): ?>
                            <?php for ($i = 0; $i < $qtd; $i++): ?>
                                <span class="item-check" style="cursor: pointer; user-select: none;">❌</span>
                            <?php endfor; ?>
                        <?php else: ?>
                            ➖
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        
        <div id="flash-effect"></div>
        
        <script>
        let scrollVelocidade = 0.8; 
        let delayNoTopo = 3000;   

        function iniciarScrollAutomatico() {
            let posicaoAtual = window.scrollY;
            let alturaTotal = document.body.scrollHeight;
            let alturaJanela = window.innerHeight;

            if (posicaoAtual + alturaJanela < alturaTotal) {
                window.scrollBy(0, scrollVelocidade); 
                requestAnimationFrame(iniciarScrollAutomatico); 
            } else {
               
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    setTimeout(iniciarScrollAutomatico, delayNoTopo);
                }, 2000); 
            }
        }

        window.onload = iniciarScrollAutomatico;
       

       function playSound() {
            const audio = new Audio('./audios/som-sucesso.mp3');
            audio.play();
        }
        document.querySelectorAll('.item-check').forEach(item => {
            item.addEventListener('click', function() {
                if (this.innerText === '❌') {
                    this.innerText = '✅';
                    this.style.cursor = 'default';

                    const flash = document.getElementById('flash-effect');
                    flash.classList.add('flash-active');
                    
                    setTimeout(() => {
                        flash.classList.remove('flash-active');
                    }, 1000);

                    somSucesso.currentTime = 0; 
                    playSound();
                } else {
                    this.innerText = '❌';
                    this.style.cursor = 'pointer';
                }
            });
        });
       
        </script>
    </tbody>
</table>
</body>
</html>
