<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadro Pós-Venda</title>
<style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; color: #333; margin: 25px; }
        .header-painel { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h1 { color: #2c3e50; margin: 0; font-size: 28px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th { background-color: #2c3e50; color: white; padding: 15px; text-align: left; font-size: 16px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eef2f5; font-size: 16px; }
        tr:hover { background-color: #f8fafc; }
        .btn-baixa { background-color: #27ae60; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.2s; font-size: 14px; }
        .btn-baixa:hover { background-color: #219150; }
        .badge-pronto { background-color: #d4edda; color: #155724; padding: 6px 12px; border-radius: 5px; font-size: 14px; font-weight: bold; border: 1px solid #c3e6cb; }
        .sem-pedidos { text-align: center; padding: 50px; color: #7f8c8d; font-size: 18px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="header-painel">
        <h1>Painel Pós-Venda: Prontos para Expedição</h1>
        <div style="font-weight: bold; color: #7f8c8d;">Status de Saídas</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Pedido / OS</th>
                <th>Prazo de Produção</th>
                <th>Conluído em</th>
                <th>Status da Fabricação</th>
                <th>Ações de Despacho</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="5" class="Sem pedidos">Nenhum pedido aguardando liberação.</td>
                </tr>
            <?php else: ?>
                <?php foreach($pedidos as $p): ?>
                <tr id="Linha-<? $p['id']?>">
                    <td style="font-weight: bold; color: #2980b9; font-size: 18px;"><?= htmlspecialchars($p['numero_pedido']) ?></td>
                    <td><?= htmlspecialchars($p['prazo_producao']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['data_conclusao'])) ?></td>
                    <td><span class="badge-pronto">100% Embalado</span></td>
                    <td>
                        <button class="btn-baixa" onclick="liberarPedido(<?= $p['id'] ?>)">Dar Baixa / Despachado</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif;?>
        </tbody>
    </table>
    <script>
            function liberarPedido(id) {
            if (confirm("Confirmar que este pedido foi liberado, faturado ou despachado para o cliente?")) {
                fetch(`dar_baixa_posvenda.php?id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const linha = document.getElementById(`linha-${id}`);
                            linha.style.transition = "all 0.5s ease";
                            linha.style.opacity = "0";
                            linha.style.background = "#e8f5e9";
                            setTimeout(() => {
                                linha.remove();
                                if (document.querySelectorAll('tbody tr').length === 0) {
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

        setInterval(() => {
            window.location.reload();
        }, 5000);
    </script>
</body>
</html>