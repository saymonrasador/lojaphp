<!DOCTYPE HTML>

<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Loja Virtual</title>
	<link rel="stylesheet" href="libs/css/global.css">
	<?php if (isset($page_css)): ?>
		<?php foreach ((array)$page_css as $css): ?>
			<link rel="stylesheet" href="<?php echo $css; ?>">
		<?php endforeach; ?>
	<?php endif; ?>
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
				echo "<a href='carrinho.php'><button>Carrinho</button></a> ";
				echo "<a href='usuarios.php'><button>Usuários</button></a> ";
				if (isset($_SESSION["fornecedor_id"]) && $_SESSION["fornecedor_id"]) {
					echo "<a href='fornecedores.php'><button>Fornecedores</button></a> ";
					echo "<a href='produtos.php'><button>Produtos</button></a> ";
					echo "<a href='estoque.php'><button>Estoque</button></a>";
				}
				echo "</nav>";
				echo "<span>Olá,<strong>" . $_SESSION["nome_usuario"] . "</strong>";		
				echo "<a href='perfil.php'> Perfil </a>";
				echo "<a href='executa_logout.php'> Logout </a></span>";
				echo "<nav>";
			} else {
				echo "<span>";
				echo "<a href='carrinho.php'> Carrinho </a>";
				echo "<a href='login.php'> Login </a>";
				echo "<a href='registro.php'> Registrar </a>";
				echo "</span>";
			}
			?>	
			</div>
		</div>
	</header>

