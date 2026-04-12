<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../FornecedorDao.php';

class PostgresFornecedorDao extends PostgresDAO implements FornecedorDao {

    private $table_name = 'fornecedores';

    public function insere($fornecedor) {
        $query = "INSERT INTO {$this->table_name}
                  (nome, descricao, telefone, email, endereco_id)
                  VALUES (:nome, :descricao, :telefone, :email, :endereco_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $fornecedor->getNome());
        $stmt->bindValue(":descricao", $fornecedor->getDescricao());
        $stmt->bindValue(":telefone", $fornecedor->getTelefone());
        $stmt->bindValue(":email", $fornecedor->getEmail());
        $stmt->bindValue(":endereco_id", $fornecedor->getEndereco()?->getId());

        return $stmt->execute();
    }

    public function altera(&$fornecedor) {
        $query = "UPDATE {$this->table_name}
                  SET nome = :nome,
                      descricao = :descricao,
                      telefone = :telefone,
                      email = :email,
                      endereco_id = :endereco_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $fornecedor->getNome());
        $stmt->bindValue(":descricao", $fornecedor->getDescricao());
        $stmt->bindValue(":telefone", $fornecedor->getTelefone());
        $stmt->bindValue(":email", $fornecedor->getEmail());
        $stmt->bindValue(":endereco_id", $fornecedor->getEndereco()?->getId());
        $stmt->bindValue(":id", $fornecedor->getId());

        return $stmt->execute();
    }

    public function removePorId($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
    public function remove($fornecedor) {
        return $this->removePorId($fornecedor->getId());
    }
    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $f = new Fornecedor();
            $f->setId($row['id']);
            $f->setNome($row['nome']);
            $f->setDescricao($row['descricao']);
            $f->setTelefone($row['telefone']);
            $f->setEmail($row['email']);
            return $f;
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
            $f = new Fornecedor();
            $f->setId($row['id']);
            $f->setNome($row['nome']);
            $f->setDescricao($row['descricao']);
            $lista[] = $f;
        }

        return $lista;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $f = new Fornecedor();
            $f->setId($row['id']);
            $f->setNome($row['nome']);
            $f->setDescricao($row['descricao']);
            $lista[] = $f;
        }

        return $lista;
    }
}
?>