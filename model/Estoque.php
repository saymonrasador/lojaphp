<?php

class Estoque
{
    private $id;
    private $produto_id;
    private $quantidade;
    private $preco;
    private $produto_nome; // campo extra para joins

    public function __construct($id = null, $produto_id = null, $quantidade = null, $preco = null)
    {
        $this->id = $id;
        $this->produto_id = $produto_id;
        $this->quantidade = $quantidade;
        $this->preco = $preco;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getProdutoId() { return $this->produto_id; }
    public function setProdutoId($produto_id) { $this->produto_id = $produto_id; }

    public function getQuantidade() { return $this->quantidade; }
    public function setQuantidade($quantidade) { $this->quantidade = $quantidade; }

    public function getPreco() { return $this->preco; }
    public function setPreco($preco) { $this->preco = $preco; }

    public function getProdutoNome() { return $this->produto_nome; }
    public function setProdutoNome($nome) { $this->produto_nome = $nome; }
}