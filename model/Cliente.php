<?php

class Cliente
{
    private $id;
    private $nome;
    private $telefone;
    private $email;
    private $cartaoCredito;
    private $endereco; // objeto Endereco

    public function __construct($id, $nome, $telefone, $email, CartaoCredito $cartaoCredito, Endereco $endereco)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->senha = $senha;
        $this->endereco = $endereco;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getTelefone() { return $this->telefone; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getCartaoCredito() { return $this->cartaoCredito; }
    public function setCartaoCredito(CartaoCredito $cartaoCredito) { $this->cartaoCredito = $cartaoCredito; }

    public function getEndereco() { return $this->endereco; }
    public function setEndereco(Endereco $endereco) { $this->endereco = $endereco; }
}