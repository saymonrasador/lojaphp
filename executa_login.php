<?php 
// Métodos de acesso ao banco de dados 
require "fachada.php"; 
 
// Inicia sessão 
session_start();

// Recupera o login 
$login = isset($_POST["login"]) ? addslashes(trim($_POST["login"])) : FALSE; 
$senha = isset($_POST["senha"]) ? md5(trim($_POST["senha"])) : FALSE;
 
// Usuário não forneceu a senha ou o login 
if(!$login || !$senha) {
    echo "Login fornecido: " . ($login ? "SIM" : "NÃO") . "<br>";
    echo "Senha fornecida: " . (isset($_POST["senha"]) && $_POST["senha"] !== "" ? "SIM" : "NÃO") . "<br>";
    echo "Você deve digitar sua senha e login!<br>"; 
    echo "<a href='login.php'>Efetuar Login</a>";
    exit; 
}  

$dao = $factory->getUsuarioDao();
$usuario = $dao->buscaPorLogin($login);

// Usuário não existe
if(!$usuario) {
    $_SESSION["erro"] = "Usuário não encontrado!"; 
    header("Location: login.php"); 
    exit; 
}

// Verifica a senha 
if(!strcmp($senha, $usuario->getSenha())) 
{ 
    // TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário 
    $_SESSION["id_usuario"] = $usuario->getId(); 
    $_SESSION["nome_usuario"] = stripslashes($usuario->getNome());
    unset($_SESSION["erro"]); // Remove qualquer mensagem de erro anterior
    //$_SESSION["permissao"]= $dados["postar"]; 
    header("Location: index.php"); 
    exit; 
} else {
    // Senha incorreta
    $_SESSION["erro"] = "Senha incorreta!"; 
    header("Location: login.php"); 
    exit; 
}
?>
