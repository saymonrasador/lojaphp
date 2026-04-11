<?php
// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroi todas as variáveis de sessão
$_SESSION = array();

// Destroi a sessão
session_destroy();

// Redireciona para a página inicial
header("Location: index.php");
exit;
?>
