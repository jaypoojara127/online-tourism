<?php
require_once 'config.php';

class Database {
    private $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function executeQuery($sql) {
        try {
            $result = $this->conn->query($sql);
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Create global database instance
$db = new Database();
$conn = $db->getConnection();
?>