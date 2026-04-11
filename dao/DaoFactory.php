<?php
abstract class DaoFactory {

    protected abstract function getConnection();
    public abstract function getUsuarioDao();
    public abstract function getProdutoDao();
    public abstract function getPedidoDao();
    public abstract function getItemPedidoDao();
    public abstract function getClienteDao();
    public abstract function getFornecedorDao();
    public abstract function getEnderecoDao();
    public abstract function getEstoqueDao();
}
?>