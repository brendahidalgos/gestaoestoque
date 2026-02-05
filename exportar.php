<?php
include 'db.php';

// Define o nome do arquivo
$filename = "estoque_detalhado_" . date('Y-m-d') . ".csv";

// Configura os cabeçalhos para download e formato CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Adiciona o BOM (Byte Order Mark) para o Excel reconhecer acentos e R$ corretamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho das colunas (incluindo a Margem de Lucro)
// Usamos o ";" para que o Excel separe as colunas automaticamente no Brasil
fputcsv($output, array('Código', 'Produto', 'Quantidade', 'Preço Compra', 'Preço Revenda', 'Margem (%)', 'Data Cadastro'), ';');

// Busca os dados do banco
$query = "SELECT codigo, nome, quantidade, preco_compra, preco_revenda, data_criacao FROM produtos ORDER BY nome ASC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    // Cálculo da margem para o Excel
    $lucro = $row['preco_revenda'] - $row['preco_compra'];
    $margem_valor = ($row['preco_revenda'] > 0) ? ($lucro / $row['preco_revenda']) * 100 : 0;
    
    // Formata a margem com uma casa decimal e o símbolo %
    $margem_formatada = number_format($margem_valor, 1, ',', '.') . '%';

    // Organiza a linha para o CSV
    $linha = array(
        $row['codigo'],
        $row['nome'],
        $row['quantidade'],
        'R$ ' . number_format($row['preco_compra'], 2, ',', '.'),
        'R$ ' . number_format($row['preco_revenda'], 2, ',', '.'),
        $margem_formatada,
        $row['data_criacao']
    );

    fputcsv($output, $linha, ';');
}

fclose($output);
exit;
?>