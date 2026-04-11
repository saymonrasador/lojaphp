<?php

interface ProdutoDao {
    public function insere($produto);
    public function remove($produto);
    public function removePorId($id);
    public function altera(&$produto);
    public function buscaPorId($id);
    public function buscaPorNome($nome);
    public function buscaPorFornecedor($fornecedorId);
    public function buscaTodos();
}
?>