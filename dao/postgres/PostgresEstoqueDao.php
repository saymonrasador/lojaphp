<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../EstoqueDao.php';

class PostgresEstoqueDao extends PostgresDAO implements EstoqueDao {

    private $table_name = 'estoques';

    public function insere($estoque) {
        $query = "INSERT INTO {$this->table_name}
                  (produto_id, quantidade, preco)
                  VALUES (:produto_id, :quantidade, :preco)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":produto_id", $estoque->getProdutoId());
        $stmt->bindValue(":quantidade", $estoque->getQuantidade());
        $stmt->bindValue(":preco", $estoque->getPreco());

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $e = new Estoque($row['id'], $row['produto_id'], $row['quantidade'], $row['preco']);
            return $e;
        }
        return null;
    }

    public function buscaPorProduto($produtoId) {
        $query = "SELECT * FROM {$this->table_name} WHERE produto_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $produtoId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $e = new Estoque($row['id'], $row['produto_id'], $row['quantidade'], $row['preco']);
            return $e;
        }
        return null;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $e = new Estoque($row['id'], $row['produto_id'], $row['quantidade'], $row['preco']);
            $lista[] = $e;
        }

        return $lista;
    }

    public function removePorId($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function remove($estoque) {
        return $this->removePorId($estoque->getId());
    }

    public function altera(&$estoque) {
        $query = "UPDATE {$this->table_name} 
                  SET produto_id = :produto_id, quantidade = :quantidade, preco = :preco
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":produto_id", $estoque->getProdutoId());
        $stmt->bindValue(":quantidade", $estoque->getQuantidade());
        $stmt->bindValue(":preco", $estoque->getPreco());
        $stmt->bindValue(":id", $estoque->getId());

        return $stmt->execute();
    }

    public function baixaEstoque($produtoId, $quantidade) {
        $query = "UPDATE {$this->table_name}
                  SET quantidade = quantidade - :qtd
                  WHERE produto_id = :produto_id
                  AND quantidade >= :qtd";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":qtd", $quantidade);
        $stmt->bindValue(":produto_id", $produtoId);

        return $stmt->execute();
    }
}
?>