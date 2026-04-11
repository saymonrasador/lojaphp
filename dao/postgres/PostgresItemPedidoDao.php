<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../ItemPedidoDao.php';

class PostgresItemPedidoDao extends PostgresDAO implements ItemPedidoDao {

    private $table_name = 'itens_pedido';

    public function insere($item) {
        $query = "INSERT INTO {$this->table_name}
                  (pedido_id, produto_id, quantidade, preco)
                  VALUES (:pedido_id, :produto_id, :quantidade, :preco)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":pedido_id", $item->getPedidoId());
        $stmt->bindValue(":produto_id", $item->getProduto()->getId());
        $stmt->bindValue(":quantidade", $item->getQuantidade());
        $stmt->bindValue(":preco", $item->getPreco());

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $produtoDao = new PostgresProdutoDao($this->conn);
            $i = new ItemPedido($row['id'], $row['quantidade'], $row['preco'], $produtoDao->buscaPorId($row['produto_id']));
            return $i;
        }
        return null;
    }

    public function buscaPorPedidoId($pedidoId) {
        $query = "SELECT * FROM {$this->table_name} WHERE pedido_id = ? ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pedidoId);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtoDao = new PostgresProdutoDao($this->conn);
            $i = new ItemPedido($row['id'], $row['quantidade'], $row['preco'], $produtoDao->buscaPorId($row['produto_id']));
            $lista[] = $i;
        }

        return $lista;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtoDao = new PostgresProdutoDao($this->conn);
            $i = new ItemPedido($row['id'], $row['quantidade'], $row['preco'], $produtoDao->buscaPorId($row['produto_id']));
            $lista[] = $i;
        }

        return $lista;
    }

    public function removePorId($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function remove($item) {
        return $this->removePorId($item->getId());
    }

    public function altera(&$item) {
        $query = "UPDATE {$this->table_name} 
                  SET pedido_id = :pedido_id, produto_id = :produto_id, quantidade = :quantidade, preco = :preco
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":pedido_id", $item->getPedidoId());
        $stmt->bindValue(":produto_id", $item->getProduto()->getId());
        $stmt->bindValue(":quantidade", $item->getQuantidade());
        $stmt->bindValue(":preco", $item->getPreco());
        $stmt->bindValue(":id", $item->getId());

        return $stmt->execute();
    }
}
?>