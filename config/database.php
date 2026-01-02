<?php
/*
  Database Configuration
 */


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kayan_medical');
define('DB_PORT', 3306);

// Create connection
class Database {
    private $conn;

    public function connect() {
        $this->conn = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            DB_PORT
        );

        if ($this->conn->connect_error) {
            die('Connection Failed: ' . $this->conn->connect_error);
        }

        return $this->conn;
    }

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

$database = new Database();
$conn = $database->connect();
?>