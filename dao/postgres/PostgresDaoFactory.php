<?php

include_once(__DIR__ . '/../DaoFactory.php');
include_once('PostgresClienteDao.php');
include_once('PostgresEnderecoDao.php');
include_once('PostgresProdutoDao.php');
include_once('PostgresEstoqueDao.php');
include_once('PostgresPedidoDao.php');
include_once('PostgresItemPedidoDao.php');
include_once('PostgresFornecedorDao.php');
include_once('PostgresUsuarioDao.php');

class PostgresDaoFactory extends DaoFactory {

    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "loja_virtual";
    private $port = "5432";
    private $username = "postgres";
    private $password = "ucs";
    public $conn;
  
    // get the database connection
    public function getConnection(){
        $this->conn = null;
        try{
            $this->conn = new PDO("pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
      }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function getClienteDao() {
        return new PostgresClienteDao($this->getConnection());
    }

    public function getEnderecoDao() {
        return new PostgresEnderecoDao($this->getConnection());
    }

    public function getProdutoDao() {
        return new PostgresProdutoDao($this->getConnection());
    }

    public function getEstoqueDao() {
        return new PostgresEstoqueDao($this->getConnection());
    }  

    public function getPedidoDao() {
        return new PostgresPedidoDao($this->getConnection());
    }

    public function getItemPedidoDao() {
        return new PostgresItemPedidoDao($this->getConnection());
    }

    public function getFornecedorDao() {
        return new PostgresFornecedorDao($this->getConnection());
    }

    public function getUsuarioDao() {
        return new PostgresUsuarioDao($this->getConnection());
    }

}
?>
