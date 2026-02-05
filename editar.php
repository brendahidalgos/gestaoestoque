<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE produtos SET codigo=?, nome=?, quantidade=?, preco_compra=?, preco_revenda=? WHERE id=?");
    $stmt->bind_param("ssiddi", $_POST['codigo'], $_POST['nome'], $_POST['quantidade'], $_POST['preco_compra'], $_POST['preco_revenda'], $id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="estilo/style.css">
    <style>
        /* Estilização Premium para o Card de Edição */
        .edit-card { 
            background: white; 
            max-width: 550px; 
            margin: 10px auto; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.08); 
        }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { 
            display: block; 
            margin-bottom: 7px; 
            font-weight: 600; 
            color: #4a5568; 
            font-size: 14px;
        }
        
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #e2e8f0; 
            border-radius: 10px; 
            box-sizing: border-box; 
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        /* Painel de Inteligência de Preço */
        .info-panel { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            background: #fdfdfd; 
            padding: 20px; 
            border-radius: 15px; 
            margin: 25px 0;
            border: 1px solid #edf2f7;
        }

        .info-box small { 
            color: #718096; 
            text-transform: uppercase; 
            font-size: 10px; 
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .info-box span { 
            display: block; 
            font-size: 20px; 
            font-weight: 800; 
            margin: 5px 0;
        }

        /* Botão Aplicar Sugestão Estilizado */
        .btn-magic {
            background-color: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-magic:hover {
            background-color: #2b6cb0;
            color: white;
            transform: translateY(-1px);
        }

        .negativo { color: #e53e3e; }
        .positivo { color: #38a169; }

        /* Botões de Ação Final */
        .btn-group { display: flex; gap: 12px; margin-top: 30px; }
        .btn-save { 
            flex: 2; 
            background: #38a169; 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-save:hover { background: #2f855a; transform: translateY(-2px); }
        
        .btn-cancel { 
            flex: 1; 
            background: #f7fafc; 
            color: #718096; 
            text-align: center; 
            text-decoration: none; 
            padding: 14px; 
            border-radius: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="edit-card">
        <h2 style="color: #2d3748; margin-bottom: 5px;">✏️ Editar Produto</h2>
        <p style="color: #a0aec0; font-size: 13px; margin-bottom: 25px;">Ajuste os valores para otimizar sua margem.</p>
        
        <form method="POST" id="editForm">
            <div class="form-group">
                <label>Código do Produto</label>
                <input type="text" name="codigo" value="<?php echo $p['codigo']; ?>" required>
            </div>
            <div class="form-group">
                <label>Nome do Produto</label>
                <input type="text" name="nome" value="<?php echo $p['nome']; ?>" required>
            </div>
            <div class="form-group">
                <label>Quantidade</label>
                <input type="number" name="quantidade" value="<?php echo $p['quantidade']; ?>" required>
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Preço Compra (R$)</label>
                    <input type="number" step="0.01" name="preco_compra" id="p_compra" value="<?php echo $p['preco_compra']; ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Preço Revenda (R$)</label>
                    <input type="number" step="0.01" name="preco_revenda" id="p_venda" value="<?php echo $p['preco_revenda']; ?>" required>
                </div>
            </div>

            <div class="info-panel">
                <div class="info-box">
                    <small>Margem Atual</small>
                    <span id="margem_display">0%</span>
                </div>
                <div class="info-box">
                    <small>Sugestão (Margem 60%)</small>
                    <span id="sugestao_display" style="color: #4361ee;">R$ 0,00</span>
                    <button type="button" class="btn-magic" onclick="aplicarSugestao()">
                        Aplicar Valor
                    </button>
                </div>
            </div>

            <div class="btn-group">
                <a href="index.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-save">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <script>
        const inputCompra = document.getElementById('p_compra');
        const inputVenda = document.getElementById('p_venda');
        const displayMargem = document.getElementById('margem_display');
        const displaySugestao = document.getElementById('sugestao_display');

        function calcular() {
            const compra = parseFloat(inputCompra.value) || 0;
            const venda = parseFloat(inputVenda.value) || 0;

            if (venda > 0) {
                const margem = ((venda - compra) / venda) * 100;
                displayMargem.innerText = margem.toFixed(1) + "%";
                displayMargem.className = margem < 0 ? "negativo" : "positivo";
            } else {
                displayMargem.innerText = "0%";
            }

            const sugestao = compra / (1 - 0.60);
            displaySugestao.innerText = "R$ " + sugestao.toLocaleString('pt-br', {minimumFractionDigits: 2});
        }

        function aplicarSugestao() {
            const compra = parseFloat(inputCompra.value) || 0;
            const sugestao = compra / (1 - 0.60);
            inputVenda.value = sugestao.toFixed(2);
            calcular();
        }

        inputCompra.addEventListener('input', calcular);
        inputVenda.addEventListener('input', calcular);
        window.onload = calcular;
    </script>
</body>
</html>