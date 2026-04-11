<?php

interface ItemPedidoDao {
    public function insere($itemPedido);
    public function remove($itemPedido);
    public function removePorId($id);
    public function altera(&$itemPedido);
    public function buscaPorId($id);
    public function buscaPorPedidoId($pedidoId);
    public function buscaTodos();
}
?>