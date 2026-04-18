<?php
include_once "comum.php";
if (is_session_started() === FALSE) {
    session_start();
}

// Se não estiver logado, redireciona para a loja
if (!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"])) {
    header("Location: loja.php");
    exit;
}

$page_css = ['libs/css/index.css'];
include_once "layout_header.php";
?>

<main>
    <div>
        <section>
            <h2>Bem-vindo ao Sistema de Gestão</h2>
            <p>Olá, <strong><?php echo htmlspecialchars($_SESSION["nome_usuario"]); ?></strong>! Utilize o menu abaixo para gerenciar os cadastros.</p>
        </section>

        <section>
            <h3>Cadastros</h3>
            <p><a href="loja.php"><button>Loja de Produtos</button></a></p>
            <p><a href="usuarios.php"><button>Cadastro de Usuários</button></a></p>
            <p><a href="fornecedores.php"><button>Cadastro de Fornecedores</button></a></p>
            <p><a href="produtos.php"><button>Cadastro de Produtos</button></a></p>
            <p><a href="estoque.php"><button>Gestão de Estoque</button></a></p>
        </section>
    </div>
</main>
