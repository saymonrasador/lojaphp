<?php
include_once "layout_header.php";
$page_title = "Autenticação Obrigatória para acessar Veículos";
?>

<main>
    <div>
        <div>
            <div>
                <h2>Bem-vindo</h2>
                <p>Acesse sua conta</p>
            </div>

            <form action="executa_login.php" method="POST">
                <div>
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" placeholder="Digite seu login" required>
                </div>

                <div>
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </div>

                <button type="submit">Entrar</button>
            </form>

            <div>
                <p>Problema para acessar? <a href="index.php">Voltar para início</a></p>
            </div>
        </div>
    </div>
</main>
