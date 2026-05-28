<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expedição</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; color: #333; margin: 25px; }
        .header-painel { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h1 { color: #2c3e50; margin: 0; font-size: 28px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th { background-color: #2c3e50; color: white; padding: 15px; text-align: center; font-size: 16px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eef2f5; font-size: 16px; text-align: center; vertical-align: middle; }
        tr:hover { background-color: #f8fafc; }
        
        .btn-baixa { background-color: #27ae60; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.2s; font-size: 14px; }
        .btn-baixa:hover { background-color: #219150; }
        .badge-pronto { background-color: #d4edda; color: #155724; padding: 6px 12px; border-radius: 5px; font-size: 14px; font-weight: bold; border: 1px solid #c3e6cb; }
        .sem-pedidos { text-align: center; padding: 50px; color: #7f8c8d; font-size: 18px; font-weight: 500; }
        
        .linha-observacao { background-color: #fcfcfc; display: none; }
        .linha-observacao td { text-align: left; padding: 0 25px; border-bottom: 1px solid #e0e0e0; }
        
        .wrapper-sanfona { 
            max-height: 0; 
            overflow: hidden; 
            transition: max-height 0.4s ease-out, padding 0.4s ease; 
            padding: 0;
        }
        .linha-observacao.aberta .wrapper-sanfona { 
            max-height: 200px; 
            padding: 20px 0;
        }
        .container-obs { display: flex; gap: 15px; align-items: center; width: 100%; }
        .input-obs { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
        .input-obs:focus { border-color: #2980b9; outline: none; box-shadow: 0 0 5px rgba(41,128,185,0.2); }
        
        .btn-salvar-obs { background-color: #2980b9; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .btn-salvar-obs:hover { background-color: #216b9b; }
        
        .btn-mais { background: none; border: none; color: #2980b9; font-size: 22px; font-weight: bold; cursor: pointer; padding: 5px 10px; transition: transform 0.2s; }
        .btn-mais:hover { transform: translateY(-2px); }

        .btn-etiqueta { background-color: #2788ae; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.2s; font-size: 14px; text-decoration: none;}
        .btn-etiqueta:hover { background-color: #216191; }

        .footer {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="header-painel">
        <h1>Painel Expedição</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Prazo de Produção</th>
                <th>Concluído em</th>
                <th>Status da Fabricação</th>
                <th>Ações</th>
                <th>Mais</th>
            </tr>
        </thead>
        <tbody>
            <?php
            require_once '../config/Database.php'; 
            require_once '../Model/Sistema.php'; 

            $database = new Database();
            $db = $database->getConnection();
            $sistema = new Sistema($db);

            $pedidos = $sistema->mostrarFilaExpedicao(); 
            ?>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" class="sem-pedidos">Nenhum pedido aguardando liberação.</td>
                </tr>
            <?php else: ?>
                <?php foreach($pedidos as $p): ?>
                <tr id="Linha-<?= $p['id'] ?>">
                    <td style="font-weight: bold; color: #2980b9; font-size: 18px;"><?= htmlspecialchars($p['numero_pedido']) ?></td>
                    <td><?= htmlspecialchars(substr($p['prazo_producao'], 0, 10)) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['data_conclusao'])) ?></td>
                    <td><span class="badge-pronto">100% Embalado</span></td>
                    <td>
                        <button class="btn-baixa" onclick="liberarPedido(<?= $p['id'] ?>)">Finalizar Pedido</button>
                    </td>
                    <td>
                        <button class="btn-mais" onclick="toggleSanfona(<?= $p['id'] ?>)">...</button>
                    </td>
                </tr>
                <tr id="ObsRow-<?= $p['id'] ?>" class="linha-observacao">
                    <td colspan="6">
                        <div class="wrapper-sanfona">
                            <div class="container-obs">
                                <input type="text" 
                                       id="input-obs-<?= $p['id'] ?>" 
                                       class="input-obs" 
                                       placeholder="Observações">
                                <button class="btn-salvar-obs" onclick="salvarObservacaoLocal(<?= $p['id'] ?>)">Salvar Nota</button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function toggleSanfona(id) {
            const linhaObs = document.getElementById(`ObsRow-${id}`);
            if (!linhaObs) return;
            
            if (linhaObs.style.display === "table-row") {
                linhaObs.classList.remove('aberta');
                setTimeout(() => {
                    linhaObs.style.display = "none";
                }, 400); 
            } else {
                linhaObs.style.display = "table-row";
                
                setTimeout(() => {
                    const inputObs = document.getElementById(`input-obs-${id}`);
                    if (inputObs) {
                        const notaSalva = localStorage.getItem(`obs_pedido_${id}`);
                        inputObs.value = notaSalva ? notaSalva : "";
                    }
                    linhaObs.classList.add('aberta');
                }, 20);
            }
        }

        function salvarObservacaoLocal(id) {
            const elementoInput = document.getElementById(`input-obs-${id}`);
            
            if (!elementoInput) {
                alert("Erro ao identificar o campo de digitação.");
                return;
            }

            const txtObs = elementoInput.value;
            localStorage.setItem(`obs_pedido_${id}`, txtObs);
            
            alert("Observação guardada no navegador deste computador!");
            toggleSanfona(id);
        }

        function liberarPedido(id) {
            if (confirm("Confirmar que este pedido foi liberado?")) {
                fetch(`../Function/dar_baixa_expedicao.php?id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const linha = document.getElementById(`Linha-${id}`);
                            const linhaObs = document.getElementById(`ObsRow-${id}`);
                            
                            localStorage.removeItem(`obs_pedido_${id}`);
                            
                            if(linha) {
                                linha.style.transition = "all 0.5s ease";
                                linha.style.opacity = "0";
                                linha.style.background = "#e8f5e9";
                            }
                            if(linhaObs) {
                                linhaObs.style.transition = "all 0.5s ease";
                                linhaObs.style.opacity = "0";
                            }
                            
                            setTimeout(() => {
                                if(linha) linha.remove();
                                if(linhaObs) linhaObs.remove();
                                
                                if (document.querySelectorAll('tbody tr:not(.linha-observacao)').length === 0) {
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            alert("Erro ao dar baixa no sistema: " + data.error);
                        }
                    })
                    .catch(err => console.error("Erro na comunicação:", err));
            }
        }

         let idsAtuais = Array.from(document.querySelectorAll('tbody tr[id^="linha-"]'))
                        .map(tr => tr.id.replace('linha-', ''));

        function verificarAtualizacoesEmSegundoPlano() {
            if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                return; 
            }

            fetch('../Function/dados_tabelas.php?tela=expedicao')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const novosDados = data.dados;
                        const novosIds = novosDados.map(p => p.id.toString());

                        const temNovoItem = novosIds.some(id => !idsAtuais.includes(id));
                        const itemSumiu = idsAtuais.some(id => !novosIds.includes(id));

                        if (temNovoItem || itemSumiu) {
                            window.location.reload();
                            return
                        }
                    }
                })
                .catch(err => console.error("Erro na sincronização rápida:", err));
        }
        setInterval(verificarAtualizacoesEmSegundoPlano, 2000);
    </script>
    <div class="footer">
        Painel Operacional EQUIPILATES &copy; <?= date('Y'); ?>
    </div>
</body>
</html>