<?php

class ItemPedido
{
    private $id;
    private $quantidade;
    private $preco;
    private $produto; // objeto Produto

    public function __construct($id, $quantidade, $preco, Produto $produto)
    {
        $this->id = $id;
        $this->quantidade = $quantidade;
        $this->preco = $preco;
        $this->produto = $produto;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getQuantidade() { return $this->quantidade; }
    public function setQuantidade($quantidade) { $this->quantidade = $quantidade; }

    public function getPreco() { return $this->preco; }
    public function setPreco($preco) { $this->preco = $preco; }

    public function getProduto() { return $this->produto; }
    public function setProduto(Produto $produto) { $this->produto = $produto; }
}