<?php
$page_css = ['libs/css/login.css'];
include_once "layout_header.php";
include_once "comum.php";

if (is_session_started() === FALSE) {
    session_start();
}

$page_title = "Autenticação Obrigatória para acessar Veículos";
?>

<main>
    <div>
        <div>
            <div>
                <h2>Bem-vindo</h2>
                <p>Acesse sua conta</p>
            </div>

            <div id="mensagens">
            <?php
                // Exibe mensagens de erro se houver
                if(isset($_SESSION["erro"]) && !empty($_SESSION["erro"])) {
                    echo "<div style='color: red;'><strong>Erro:</strong><br>";
                    echo htmlspecialchars($_SESSION["erro"]);
                    echo "</div>";
                    unset($_SESSION["erro"]);
                }
                // Exibe mensagens de sucesso se houver
                if(isset($_SESSION["mensagem"]) && !empty($_SESSION["mensagem"])) {
                    echo "<div style='color: green;'><strong>Sucesso:</strong><br>";
                    echo htmlspecialchars($_SESSION["mensagem"]);
                    echo "</div>";
                    unset($_SESSION["mensagem"]);
                }
            ?>
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

