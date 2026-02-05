<?php
include 'db.php';

if (isset($_GET['id']) && isset($_GET['preco'])) {
    $id = $_GET['id'];
    $novo_preco = $_GET['preco'];

    $stmt = $conn->prepare("UPDATE produtos SET preco_revenda = ? WHERE id = ?");
    $stmt->bind_param("di", $novo_preco, $id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
    }
}
?>