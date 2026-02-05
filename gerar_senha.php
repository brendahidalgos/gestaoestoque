<?php
// Digite a nova senha desejada aqui:
$nova_senha = 'SUA_NOVA_SENHA_AQUI'; 

echo "Copie este código e cole no campo 'senha' da tabela usuarios no phpMyAdmin:<br><br>";
echo password_hash($nova_senha, PASSWORD_DEFAULT);
?>