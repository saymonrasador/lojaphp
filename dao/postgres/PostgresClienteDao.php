<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../ClienteDao.php';

class PostgresClienteDao extends PostgresDAO implements ClienteDao {

    private $table_name = 'clientes';

    public function insere($cliente) {
        $query = "INSERT INTO {$this->table_name}
                  (nome, telefone, email, cartao_credito, endereco_id)
                  VALUES (:nome, :telefone, :email, :cartao_credito, :endereco_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $cliente->getNome());
        $stmt->bindValue(":telefone", $cliente->getTelefone());
        $stmt->bindValue(":email", $cliente->getEmail());
        $stmt->bindValue(":cartao_credito", $cliente->getCartaoCredito());
        $stmt->bindValue(":endereco_id", $cliente->getEndereco() ? (is_object($cliente->getEndereco()) ? $cliente->getEndereco()->getId() : $cliente->getEndereco()) : null);

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $c = new Cliente();
            $c->setId($row['id']);
            $c->setNome($row['nome']);
            $c->setTelefone($row['telefone']);
            $c->setEmail($row['email']);
            $c->setCartaoCredito($row['cartao_credito']);
            if (!empty($row['endereco_id'])) {
                $c->setEndereco($row['endereco_id']);
            }
            return $c;
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
            $c = new Cliente();
            $c->setId($row['id']);
            $c->setNome($row['nome']);
            $lista[] = $c;
        }
        return $lista;
    }

    public function buscaPorEmail($email) {
        $query = "SELECT * FROM {$this->table_name} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $c = new Cliente();
            $c->setId($row['id']);
            $c->setNome($row['nome']);
            $c->setTelefone($row['telefone']);
            $c->setEmail($row['email']);
            $c->setCartaoCredito($row['cartao_credito']);
            return $c;
        }
        return null;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $c = new Cliente();
            $c->setId($row['id']);
            $c->setNome($row['nome']);
            $c->setTelefone($row['telefone']);
            $c->setEmail($row['email']);
            $c->setCartaoCredito($row['cartao_credito']);
            $lista[] = $c;
        }
        return $lista;
    }

    public function removePorId($id) {
        $query = "DELETE FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    public function remove($cliente) {
        return $this->removePorId($cliente->getId());
    }

    public function altera(&$cliente) {
        $query = "UPDATE {$this->table_name} 
                  SET nome = :nome, telefone = :telefone, email = :email, cartao_credito = :cartao_credito, endereco_id = :endereco_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":nome", $cliente->getNome());
        $stmt->bindValue(":telefone", $cliente->getTelefone());
        $stmt->bindValue(":email", $cliente->getEmail());
        $stmt->bindValue(":cartao_credito", $cliente->getCartaoCredito());
        $stmt->bindValue(":endereco_id", $cliente->getEndereco() ? (is_object($cliente->getEndereco()) ? $cliente->getEndereco()->getId() : $cliente->getEndereco()) : null);
        $stmt->bindValue(":id", $cliente->getId());

        return $stmt->execute();
    }
}
?>