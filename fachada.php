<?php

error_reporting(E_ERROR | E_PARSE);

include_once('model/Cliente.php');
include_once('model/Endereco.php');
include_once('model/Estoque.php');
include_once('model/Fornecedor.php');
include_once('model/ItemPedido.php');
include_once('model/Pedido.php');
include_once('model/Produto.php');
include_once('model/Usuario.php');
include_once('dao/DaoFactory.php');
include_once('dao/postgres/PostgresDaoFactory.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$factory = new PostgresDaoFactory();


?>
