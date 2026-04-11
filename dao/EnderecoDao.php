<?php

interface EnderecoDao {
    public function insere($endereco);
    public function remove($endereco);
    public function removePorId($id);
    public function altera(&$endereco);
    public function buscaPorId($id);
    public function buscaTodos();
}
?>