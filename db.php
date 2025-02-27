<?php
class Config {
    private $host = 'localhost';
    private $username = 'root';
    private $password = 'punenexus123';
    private $dbname = 'Asystem';
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
?>