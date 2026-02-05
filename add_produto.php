<?php
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $nome = $_POST['nome'];
    $quantidade = $_POST['quantidade'];
    $p_compra = $_POST['preco_compra'];
    $p_revenda = $_POST['preco_revenda'];

    $stmt = $conn->prepare("INSERT INTO produtos (codigo, nome, quantidade, preco_compra, preco_revenda) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidd", $codigo, $nome, $quantidade, $p_compra, $p_revenda);
    $stmt->execute();
    header("Location: index.php");
}
?>