<?php
require "fachada.php";
include_once "comum.php";

if (is_session_started() === FALSE) {
    session_start();
}

// Deve estar autenticado
if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"])) {
    $_SESSION['redirect_after_login'] = 'carrinho.php';
    header("Location: login.php");
    exit;
}

// Limpa o carrinho após finalizar
$_SESSION['carrinho'] = [];

$page_css = ['libs/css/carrinho.css'];
include_once "layout_header.php";
?>

<main class="carrinho-container">
    <div class="carrinho-vazio" style="text-align:center; padding: 60px 20px;">
        <h2 style="color: green; font-size: 2em;">✅ Produto comprado!</h2>
        <p style="font-size: 1.2em; margin-top: 15px;">Obrigado pela sua compra, <strong><?php echo htmlspecialchars($_SESSION["nome_usuario"]); ?></strong>!</p>
        <a href="loja.php" class="btn-continuar" style="display:inline-block; margin-top: 20px;">Continuar Comprando</a>
    </div>
</main>
