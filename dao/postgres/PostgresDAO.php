<?php
abstract class PostgresDAO {

    protected $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function getDriver() {
        return $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function getServerVersion() {
        return $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public function getClientVersion() {
        return $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }
} 
?>




