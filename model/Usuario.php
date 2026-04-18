<?php

class Usuario
{
    private $id;
    private $nome;
    private $login;
    private $senha;
    private $clienteId;
    private $fornecedorId;

    public function __construct($id = null, $nome = null, $login = null, $senha = null, $clienteId = null, $fornecedorId = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->login = $login;
        $this->senha = $senha;
        $this->clienteId = $clienteId;
        $this->fornecedorId = $fornecedorId;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getLogin() { return $this->login; }
    public function setLogin($login) { $this->login = $login; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    public function getClienteId() { return $this->clienteId; }
    public function setClienteId($clienteId) { $this->clienteId = $clienteId; }

    public function getFornecedorId() { return $this->fornecedorId; }
    public function setFornecedorId($fornecedorId) { $this->fornecedorId = $fornecedorId; }
}