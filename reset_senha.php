<?php
include 'db.php';

// Limpa a tabela para evitar erros de ID duplicado
$conn->query("TRUNCATE TABLE usuarios");

$usuario = 'brenda';
$senha_plana = '123';
// Gera o código criptografado que o PHP exige
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
$stmt->bind_param("ss", $usuario, $senha_hash);

if ($stmt->execute()) {
    echo "<h1>✅ Sucesso!</h1>";
    echo "<p>O usuário <b>$usuario</b> foi configurado corretamente.</p>";
    echo "<p>Agora vá para a tela de login e use a senha: <b>$senha_plana</b></p>";
    echo "<a href='login.php'>Ir para Login</a>";
} else {
    echo "❌ Erro: " . $conn->error;
}
?>