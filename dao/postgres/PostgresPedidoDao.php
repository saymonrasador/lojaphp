<?php

include_once('PostgresDAO.php');
require_once __DIR__ . '/../PedidoDao.php';

class PostgresPedidoDao extends PostgresDAO implements PedidoDao {

    private $table_name = 'pedidos';

    public function insere($pedido) {
        $query = "INSERT INTO {$this->table_name}
                  (numero, cliente_id, situacao)
                  VALUES (:numero, :cliente_id, :situacao)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":numero", $pedido->getNumero());
        $stmt->bindValue(":cliente_id", $pedido->getClienteId());
        $stmt->bindValue(":situacao", $pedido->getSituacao());

        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $p = new Pedido($row['id'], $row['numero'], $row['cliente_id'], $row['data_pedido'], $row['data_entrega'], $row['data_cancelamento'], $row['situacao']);
            return $p;
        }
        return null;
    }

    public function buscaPorNumero($numero) {
        $query = "SELECT * FROM {$this->table_name} WHERE numero = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $numero);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $p = new Pedido($row['id'], $row['numero'], $row['cliente_id'], $row['data_pedido'], $row['data_entrega'], $row['data_cancelamento'], $row['situacao']);
            return $p;
        }
        return null;
    }

    public function buscaPorClienteNome($nomeCliente) {
        $query = "SELECT p.* FROM {$this->table_name} p
                  INNER JOIN clientes c ON p.cliente_id = c.id
                  WHERE c.nome ILIKE ?
                  ORDER BY p.numero DESC";
        $stmt = $this->conn->prepare($query);
        
        $nomeCliente = "%$nomeCliente%";
        $stmt->bindParam(1, $nomeCliente);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $p = new Pedido($row['id'], $row['numero'], $row['cliente_id'], $row['data_pedido'], $row['data_entrega'], $row['data_cancelamento'], $row['situacao']);
            $lista[] = $p;
        }

        return $lista;
    }

    public function buscaTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY numero DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $lista = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $p = new Pedido($row['id'], $row['numero'], $row['cliente_id'], $row['data_pedido'], $row['data_entrega'], $row['data_cancelamento'], $row['situacao']);
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

    public function remove($pedido) {
        return $this->removePorId($pedido->getId());
    }

    public function altera(&$pedido) {
        $query = "UPDATE {$this->table_name} 
                  SET numero = :numero, cliente_id = :cliente_id, data_entrega = :data_entrega, 
                      data_cancelamento = :data_cancelamento, situacao = :situacao
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":numero", $pedido->getNumero());
        $stmt->bindValue(":cliente_id", $pedido->getClienteId());
        $stmt->bindValue(":data_entrega", $pedido->getDataEntrega());
        $stmt->bindValue(":data_cancelamento", $pedido->getDataCancelamento());
        $stmt->bindValue(":situacao", $pedido->getSituacao());
        $stmt->bindValue(":id", $pedido->getId());

        return $stmt->execute();
    }

    public function alteraSituacao($pedidoId, $situacao) {
        $query = "UPDATE {$this->table_name} SET situacao = :situacao WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":situacao", $situacao);
        $stmt->bindValue(":id", $pedidoId);

        return $stmt->execute();
    }
}
?>