<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../UsuarioDao.php';

class PostgresUsuarioDao extends PostgresDAO implements UsuarioDao {
    private $table_name = 'usuarios';
    
    public function insere($usuario) {
        $query = "INSERT INTO " . $this->table_name . 
        " (login, senha, nome, perfil) VALUES" .
        " (:login, :senha, :nome, :perfil)";

        $stmt = $this->conn->prepare($query);

        // bind values 
        $stmt->bindValue(":login", $usuario->getLogin());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(":perfil", $usuario->getPerfil());

        try {
            if($stmt->execute()){
                return true;
            }else{
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . implode(", ", $errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Exception ao inserir usuário: " . $e->getMessage());
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

    public function remove($usuario) {
        return removePorId($usuario->getId());
    }

    public function altera(&$usuario) {
        $query = "UPDATE " . $this->table_name . 
        " SET login = :login, senha = :senha, nome = :nome, perfil = :perfil" .
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // bind parameters
        $stmt->bindValue(":login", $usuario->getLogin());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(':id', $usuario->getId());
        $stmt->bindValue(":perfil", $usuario->getPerfil());

        // execute the query
        if($stmt->execute()){
            return true;
        }    
        return false;
    }

    public function buscaPorId($id) {
        $usuario = null;
        $query = "SELECT
                    id, login, nome, senha, perfil
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
            $usuario = new Usuario($row['id'], $row['nome'], $row['login'], $row['senha'], $row['perfil']);
        } 
        return $usuario;
    }

    public function buscaPorLogin($login) {
        $usuario = null;
        $query = "SELECT
                    id, login, nome, senha, perfil
                FROM
                    " . $this->table_name . "
                WHERE
                    login = ?
                LIMIT
                    1 OFFSET 0";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $login);
        $stmt->execute();
     
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $usuario = new Usuario($row['id'], $row['nome'], $row['login'], $row['senha'], $row['perfil']);
        } 
        return $usuario;
    }

    public function buscaTodos() {
        $usuarios = array();
        $query = "SELECT
                    id, login, senha, nome
                FROM
                    " . $this->table_name . 
                    " ORDER BY id ASC";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $usuarios[] = new Usuario($id, $nome, $login, $senha, isset($perfil) ? $perfil : 'INTERNO');
        }
        return $usuarios;
    }
}
?>