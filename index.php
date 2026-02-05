<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Lógica para os cards de resumo no topo
$stats = $conn->query("SELECT 
    COUNT(*) as total_itens, 
    SUM(quantidade) as qtd_total,
    SUM(quantidade * preco_compra) as investimento
    FROM produtos")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estoque Master</title>
    <link rel="stylesheet" href="estilo/style.css">
</head>
<body>
    <div class="container">
        <h1>📊 Sistema de Estoque</h1>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <p style="color: #666;">Bem-vinda, <strong>brenda</strong>!</p>
            <a href="logout.php" style="background: #fee2e2; color: #ef4444; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 13px; border: 1px solid #fecaca;">
                Sair do Sistema 🚪
            </a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-bottom: 4px solid var(--primary);">
                <small style="color: #888; font-weight: bold;">PRODUTOS CADASTRADOS</small>
                <h2 style="margin: 5px 0; color: var(--text);"><?php echo $stats['total_itens']; ?></h2>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-bottom: 4px solid var(--warning);">
                <small style="color: #888; font-weight: bold;">TOTAL EM ESTOQUE (UN)</small>
                <h2 style="margin: 5px 0; color: var(--text);"><?php echo $stats['qtd_total'] ?? 0; ?></h2>
            </div>
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-bottom: 4px solid var(--success);">
                <small style="color: #888; font-weight: bold;">VALOR TOTAL (COMPRA)</small>
                <h2 style="margin: 5px 0; color: var(--text);">R$ <?php echo number_format($stats['investimento'] ?? 0, 2, ',', '.'); ?></h2>
            </div>
        </div>

        <form action="add_produto.php" method="POST" class="form-cadastro">
            <input type="text" name="codigo" placeholder="Cód" required>
            <input type="text" name="nome" placeholder="Produto" required>
            <input type="number" name="quantidade" placeholder="Qtd" required>
            <input type="number" step="0.01" name="preco_compra" placeholder="Compra (R$)" required>
            <input type="number" step="0.01" name="preco_revenda" placeholder="Revenda (R$)" required>
            <button type="submit">Cadastrar</button>
        </form>

        <hr>

        <div class="busca-container">
            <input type="text" id="inputBusca" placeholder="🔍 Buscar por nome ou código..." onkeyup="filtrarEstoque()">
        </div>

        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <p style="color: #666; font-size: 14px;">Gerencie seus itens em tempo real.</p>
            <a href="exportar.php" class="btn-exportar">📊 Exportar para Excel</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Cód</th>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>V. Compra</th>
                    <th>V. Venda</th>
                    <th>Margem</th>
                    <th>Sugestão (60%)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaEstoque">
                <?php
                $result = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
                while($row = $result->fetch_assoc()): 
                    $lucro = $row['preco_revenda'] - $row['preco_compra'];
                    $margem = ($row['preco_revenda'] > 0) ? ($lucro / $row['preco_revenda']) * 100 : 0;
                    
                    // Sugestão 60%
                    $preco_sugerido = $row['preco_compra'] / (1 - 0.60);

                    // Alerta de estoque baixo e cor da margem
                    $classe_margem = ($margem < 0) ? 'margem-negativa' : 'badge-lucro';
                    $classe_estoque = ($row['quantidade'] < 5) ? 'baixo-estoque' : '';
                ?>
                    <tr class="<?php echo $classe_estoque; ?>">
                        <td><?php echo $row['codigo']; ?></td>
                        <td><?php echo $row['nome']; ?></td>
                        <td><?php echo $row['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($row['preco_compra'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($row['preco_revenda'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="<?php echo $classe_margem; ?>">
                                <?php echo number_format($margem, 1); ?>%
                            </span>
                        </td>
                        <td>
                            <strong style="color: var(--primary);">
                                R$ <?php echo number_format($preco_sugerido, 2, ',', '.'); ?>
                            </strong>
                            <br>
                            <a href="atualizar_preco.php?id=<?php echo $row['id']; ?>&preco=<?php echo $preco_sugerido; ?>" 
                               style="font-size: 10px; color: #28a745; text-decoration: none; font-weight: bold;"
                               onclick="return confirm('Aplicar preço sugerido de R$ <?php echo number_format($preco_sugerido, 2, ',', '.'); ?>?')">
                               [Aplicar]
                            </a>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-editar">✏️</a>
                            <a href="deletar.php?id=<?php echo $row['id']; ?>" class="btn-deletar" onclick="return confirm('Excluir?')">❌</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function filtrarEstoque() {
        const input = document.getElementById('inputBusca');
        const filtro = input.value.toLowerCase();
        const tabela = document.getElementById('tabelaEstoque'); // Agora o ID está correto no tbody
        const linhas = tabela.getElementsByTagName('tr');

        for (let i = 0; i < linhas.length; i++) {
            const colunaCodigo = linhas[i].getElementsByTagName('td')[0];
            const colunaNome = linhas[i].getElementsByTagName('td')[1];
            
            if (colunaNome || colunaCodigo) {
                const textoCodigo = colunaCodigo.textContent || colunaCodigo.innerText;
                const textoNome = colunaNome.textContent || colunaNome.innerText;
                
                if (textoNome.toLowerCase().indexOf(filtro) > -1 || textoCodigo.toLowerCase().indexOf(filtro) > -1) {
                    linhas[i].style.display = "";
                } else {
                    linhas[i].style.display = "none";
                }
            }
        }
    }
    </script>
</body>
</html>