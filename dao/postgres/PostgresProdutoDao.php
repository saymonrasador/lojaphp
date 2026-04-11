<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../ProdutoDao.php';

class PostgresProdutoDao extends PostgresDAO implements ProdutoDao {

    private $table_name = 'produtos';

    public function insere($produto) {
        $query = "INSERT INTO {$this->table_name}
                  (nome, descricao, foto, fornecedor_id)
                  VALUES (:nome, :descricao, :foto, :fornecedor_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $produto->getNome());
        $stmt->bindValue(":descricao", $produto->getDescricao());
        $stmt->bindValue(":foto", $produto->getFoto(), PDO::PARAM_LOB);
        $stmt->bindValue(":fornecedor_id", $produto->getFornecedor()->getId());

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $fornecedorDao = new PostgresFornecedorDao($this->conn);
            $p = new Produto();
            $p->setId($row['id']);
            $p->setNome($row['nome']);
            $p->setDescricao($row['descricao']);
            $p->setFoto($row['foto']);
            $p->setFornecedor($fornecedorDao->buscaPorId($row['fornecedor_id']));
            return $p;
        }
        return null;
    }

    public function buscaPorNome($nome) {
        $query = "SELECT * FROM {$this->table_name} WHERE nome ILIKE ?";
        $stmt = $this->conn->prepare($query);

        $nome = "%$nome%";
        $stmt->bindParam(1, $nome);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedorDao = new PostgresFornecedorDao($this->conn);
            $p = new Produto();
            $p->setId($row['id']);
            $p->setNome($row['nome']);
            $p->setDescricao($row['descricao']);
            $p->setFoto($row['foto']);
            $p->setFornecedor($fornecedorDao->buscaPorId($row['fornecedor_id']));
            $lista[] = $p;
        }

        return $lista;
    }

    public function buscaPorFornecedor($fornecedorId) {
        $query = "SELECT * FROM {$this->table_name} WHERE fornecedor_id = ? ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fornecedorId);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedorDao = new PostgresFornecedorDao($this->conn);
            $p = new Produto();
            $p->setId($row['id']);
            $p->setNome($row['nome']);
            $p->setDescricao($row['descricao']);
            $p->setFoto($row['foto']);
            $p->setFornecedor($fornecedorDao->buscaPorId($row['fornecedor_id']));
            $lista[] = $p;
        }

        return $lista;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedorDao = new PostgresFornecedorDao($this->conn);
            $p = new Produto();
            $p->setId($row['id']);
            $p->setNome($row['nome']);
            $p->setDescricao($row['descricao']);
            $p->setFoto($row['foto']);
            $p->setFornecedor($fornecedorDao->buscaPorId($row['fornecedor_id']));
            $lista[] = $p;
        }

        return $lista;
    }

    public function removePorId($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function remove($produto) {
        return $this->removePorId($produto->getId());
    }

    public function altera(&$produto) {
        $query = "UPDATE {$this->table_name} 
                  SET nome = :nome, descricao = :descricao, foto = :foto, fornecedor_id = :fornecedor_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $produto->getNome());
        $stmt->bindValue(":descricao", $produto->getDescricao());
        $stmt->bindValue(":foto", $produto->getFoto(), PDO::PARAM_LOB);
        $stmt->bindValue(":fornecedor_id", $produto->getFornecedor()->getId());
        $stmt->bindValue(":id", $produto->getId());

        return $stmt->execute();
    }
}
?>