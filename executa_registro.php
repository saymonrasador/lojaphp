<?php
// Métodos de acesso ao banco de dados
require "fachada.php";

// Inicia sessão
session_start();

// Recupera os dados do formulário
$nome = isset($_POST["nome"]) ? addslashes(trim($_POST["nome"])) : FALSE;
$login = isset($_POST["login"]) ? addslashes(trim($_POST["login"])) : FALSE;
$senha = isset($_POST["senha"]) ? trim($_POST["senha"]) : FALSE;
$confirmaSenha = isset($_POST["confirmaSenha"]) ? trim($_POST["confirmaSenha"]) : FALSE;

// Valida os dados
$erros = array();

if (!$nome) {
    $erros[] = "Nome é obrigatório!";
}

if (!$login) {
    $erros[] = "Login é obrigatório!";
}

if (!$senha || !$confirmaSenha) {
    $erros[] = "Senha é obrigatória!";
}

if ($senha && $confirmaSenha && $senha !== $confirmaSenha) {
    $erros[] = "As senhas não coincidem!";
}

if (strlen($senha) < 6) {
    $erros[] = "A senha deve ter no mínimo 6 caracteres!";
}

// Se houver erros, volta para a página de registro
if (!empty($erros)) {
    $_SESSION["erros"] = $erros;
    header("Location: registro.php");
    exit;
}

// Verifica se o login já existe
$dao = $factory->getUsuarioDao();
$usuarioExistente = $dao->buscaPorLogin($login);

if ($usuarioExistente) {
    $_SESSION["erros"] = array("Este login já está em uso! Escolha outro.");
    header("Location: registro.php");
    exit;
}

// Cria o novo usuário (com hash da senha)
$senhaHash = md5($senha);
$usuario = new Usuario(null, $nome, $login, $senhaHash, 'INTERNO');

// Tenta inserir no banco
if ($dao->insere($usuario)) {
    // Sucesso! Faz o login automático
    $usuarioInserido = $dao->buscaPorLogin($login);
    
    if ($usuarioInserido) {
        $_SESSION["id_usuario"] = $usuarioInserido->getId();
        $_SESSION["nome_usuario"] = stripslashes($usuarioInserido->getNome());
        $_SESSION["mensagem"] = "Conta criada com sucesso! Bem-vindo!";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION["erros"] = array("Erro ao criar a conta. Verifique os logs para mais detalhes.");
    error_log("Falha ao inserir usuário: " . $login);
    header("Location: registro.php");
    exit;
}
?>
