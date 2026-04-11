<?php

interface PedidoDao {
    public function insere($pedido);
    public function remove($pedido);
    public function removePorId($id);
    public function altera(&$pedido);
    public function buscaPorId($id);
    public function buscaPorNumero($numero);
    public function buscaPorClienteNome($nomeCliente);
    public function buscaTodos();
    public function alteraSituacao($pedidoId, $situacao);
}
?>