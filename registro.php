<?php
$page_css = ['libs/css/registro.css'];
include_once "layout_header.php";
$page_title = "Criar Nova Conta";
?>

<main>
    <div>
        <div>
            <div>
                <h2>Criar Conta</h2>
                <p>Registre-se para usar o sistema</p>
            </div>

            <form action="executa_registro.php" method="POST">
                <div id="mensagens">
                <?php
                    // Exibe mensagens de erro se houver
                    if(isset($_SESSION["erros"]) && !empty($_SESSION["erros"])) {
                        echo "<div>";
                        echo "<ul>";
                        foreach($_SESSION["erros"] as $erro) {
                            echo "<li>" . htmlspecialchars($erro) . "</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                        unset($_SESSION["erros"]);
                    }
                ?>
                </div>
                <div>
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
                </div>

                <div>
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" placeholder="Digite um login" required>
                </div>

                <div>
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required minlength="6">
                </div>

                <div>
                    <label for="confirmaSenha">Confirmar Senha</label>
                    <input type="password" id="confirmaSenha" name="confirmaSenha" placeholder="Confirme sua senha" required minlength="6">
                </div>

                <button type="submit">Criar Conta</button>
            </form>

            <div>
                <p>Já tem uma conta? <a href="login.php">Efetue o login</a></p>
            </div>
        </div>
    </div>
</main>
