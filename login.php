<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['logado'] = true;
            $_SESSION['usuario_id'] = $user['id'];
            header("Location: index.php");
            exit;
        }
    }
    $erro = "Usuário ou senha inválidos!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Estoque Master</title>
    <link rel="stylesheet" href="estilo/style.css">
    <style>
        .login-card { max-width: 350px; margin: 100px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
        .login-card input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-login { width: 100%; background: #4361ee; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body style="background: #f0f2f5;">
    <div class="login-card">
        <h2>🔐 Acesso Restrito</h2>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <?php if(isset($erro)) echo "<p style='color:red; font-size:12px;'>$erro</p>"; ?>
            <button type="submit" class="btn-login">Entrar no Sistema</button>
        </form>
    </div>
</body>
</html>