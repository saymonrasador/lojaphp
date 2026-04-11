<?php 
// Inicia sessões 
include_once "comum.php";
		
session_start();
error_log("LOGIN");

// Verifica se existe os dados da sessão de login 
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"])) 
{ 
    error_log("SEM USUÀRIO LOGADO - Vai para login.php");
    // Usuário não logado! Redireciona para a página de login 
    header("Location: login.php"); 
    exit; 
} 
?>