<!DOCTYPE HTML>

<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Loja Virtual</title>
</head>

<body>
	<header>
		<div>
			<a href="index.php">
				<div>
					<h1>Loja Virtual</h1>
				</div>
			</a>
			<div id="login_info">
			<?php	
			include_once "comum.php";
			
			if ( is_session_started() === FALSE ) {
				session_start();
			}	
			
			if(isset($_SESSION["nome_usuario"])) {
				// Informações de login
				echo "<span>Logado como: <strong>" . $_SESSION["nome_usuario"] . "</strong>";		
				echo "<a href='executa_logout.php'> Logout </a></span>";
			} else {
				echo "<span>";
				echo "<a href='login.php'> Login </a>";
				echo "<a href='registro.php'> Registrar </a>";
				echo "</span>";
			}
			?>	
			</div>
		</div>
	</header>

