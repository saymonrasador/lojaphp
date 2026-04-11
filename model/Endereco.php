<?php

class Endereco
{
    private $id;
    private $rua;
    private $numero;
    private $complemento;
    private $bairro;
    private $cep;
    private $cidade;
    private $estado;

    public function __construct($id, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado)
    {
        $this->id = $id;
        $this->rua = $rua;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cep = $cep;
        $this->cidade = $cidade;
        $this->estado = $estado;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getRua() { return $this->rua; }
    public function setRua($rua) { $this->rua = $rua; }

    public function getNumero() { return $this->numero; }
    public function setNumero($numero) { $this->numero = $numero; }

    public function getComplemento() { return $this->complemento; }
    public function setComplemento($complemento) { $this->complemento = $complemento; }

    public function getBairro() { return $this->bairro; }
    public function setBairro($bairro) { $this->bairro = $bairro; }

    public function getCep() { return $this->cep; }
    public function setCep($cep) { $this->cep = $cep; }

    public function getCidade() { return $this->cidade; }
    public function setCidade($cidade) { $this->cidade = $cidade; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }
}