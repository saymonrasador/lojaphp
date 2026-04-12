<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../EnderecoDao.php';

class PostgresEnderecoDao extends PostgresDAO implements EnderecoDao {
    private $table_name = 'enderecos';
    
    public function insere($endereco) {
        $query = "INSERT INTO " . $this->table_name . 
        " (rua, numero, complemento, bairro, cep, cidade, estado) VALUES" .
        " (:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)";

        $stmt = $this->conn->prepare($query);

        // bind values 
        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cep", $endereco->getCep());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());

        try {
            if($stmt->execute()){
                return true;
            }else{
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . implode(", ", $errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Exception ao inserir endereço: " . $e->getMessage());
            return false;
        }
    }

    public function removePorId($id) {
        $query = "DELETE FROM " . $this->table_name . 
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // bind parameters
        $stmt->bindParam(':id', $id);

        // execute the query
        if($stmt->execute()){
            return true;
        }    
        return false;
    }

    public function remove($endereco) {
        return $this->removePorId($endereco->getId());
    }

    public function altera(&$endereco) {
        $query = "UPDATE " . $this->table_name . 
        " SET rua = :rua, numero = :numero, complemento = :complemento, bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado" .
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // bind parameters
        $stmt->bindValue(":rua", $endereco->getRua());
        $stmt->bindValue(":numero", $endereco->getNumero());
        $stmt->bindValue(":complemento", $endereco->getComplemento());
        $stmt->bindValue(":bairro", $endereco->getBairro());
        $stmt->bindValue(":cep", $endereco->getCep());
        $stmt->bindValue(":cidade", $endereco->getCidade());
        $stmt->bindValue(":estado", $endereco->getEstado());
        $stmt->bindValue(':id', $endereco->getId());

        // execute the query
        if($stmt->execute()){
            return true;
        }    
        return false;
    }

    public function buscaPorId($id) {
        $endereco = null;
        $query = "SELECT
                    id, rua, numero, complemento, bairro, cep, cidade, estado
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT
                    1 OFFSET 0";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $id);
        $stmt->execute();
     
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $endereco = new Endereco($row['id'], $row['rua'], $row['numero'], $row['complemento'], $row['bairro'], $row['cep'], $row['cidade'], $row['estado']);
        } 
        return $endereco;
    }

    public function buscaTodos() {
        $enderecos = array();
        $query = "SELECT
                    id, rua, numero, complemento, bairro, cep, cidade, estado
                FROM
                    " . $this->table_name . 
                    " ORDER BY id ASC";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $enderecos[] = new Endereco($id, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado);
        }
        return $enderecos;
    }
}
?>
