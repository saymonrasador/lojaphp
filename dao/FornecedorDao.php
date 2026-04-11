<?php

interface FornecedorDao {
    public function insere($fornecedor);
    public function remove($fornecedor);
    public function removePorId($id);
    public function altera(&$fornecedor);
    public function buscaPorId($id);
    public function buscaPorNome($nome);
    public function buscaTodos();
}
?>