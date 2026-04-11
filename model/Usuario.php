<?php

class Usuario
{
    private $id;
    private $nome;
    private $login;
    private $senha;
    private $perfil; // INTERNO ou ADMIN

    public function __construct($id, $nome, $login, $senha, $perfil)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->login = $login;
        $this->senha = $senha;
        $this->perfil = $perfil;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getLogin() { return $this->login; }
    public function setLogin($login) { $this->login = $login; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    public function getPerfil() { return $this->perfil; }
    public function setPerfil($perfil) { $this->perfil = $perfil; }
}