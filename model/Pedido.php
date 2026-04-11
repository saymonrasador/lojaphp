<?php

class Pedido
{
    private $id;
    private $numero;
    private $cliente_id;
    private $data_pedido;
    private $data_entrega;
    private $data_cancelamento;
    private $situacao;

    public function __construct($id, $numero, $cliente_id, $data_pedido, $data_entrega, $data_cancelamento, $situacao)
    {
        $this->id = $id;
        $this->numero = $numero;
        $this->cliente_id = $cliente_id;
        $this->data_pedido = $data_pedido;
        $this->data_entrega = $data_entrega;
        $this->data_cancelamento = $data_cancelamento;
        $this->situacao = $situacao;
        $this->valor_total = $valor_total;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNumero() { return $this->numero; }
    public function setNumero($numero) { $this->numero = $numero; }

    public function getClienteId() { return $this->cliente_id; }
    public function setClienteId($cliente_id) { $this->cliente_id = $cliente_id; }

    public function getDataPedido() { return $this->data_pedido; }
    public function setDataPedido($data_pedido) { $this->data_pedido = $data_pedido; }

    public function getDataEntrega() { return $this->data_entrega; }
    public function setDataEntrega($data_entrega) { $this->data_entrega = $data_entrega; }

    public function getDataCancelamento() { return $this->data_cancelamento; }
    public function setDataCancelamento($data_cancelamento) { $this->data_cancelamento = $data_cancelamento; }

    public function getSituacao() { return $this->situacao; }
    public function setSituacao($situacao) { $this->situacao = $situacao; }
}
?>