<?php

class Produto
{
    private $id;
    private $nome;
    private $descricao;
    private $foto; // dados binários (bytes da imagem)
    private $fornecedor; // objeto Fornecedor

    public function __construct($id = null, $nome = null, $descricao = null, $foto = null, Fornecedor $fornecedor = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->foto = $foto;
        $this->fornecedor = $fornecedor;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getDescricao() { return $this->descricao; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }

    public function getFoto() { return $this->foto; }
    public function setFoto($foto) { $this->foto = $foto; }

    public function getFornecedor() { return $this->fornecedor; }
    public function setFornecedor(Fornecedor $fornecedor) { $this->fornecedor = $fornecedor; }
}