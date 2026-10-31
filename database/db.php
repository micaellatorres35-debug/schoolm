<?php

class Database {
    // Railway will provide these, otherwise they fall back to your local setup
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        // Look for Railway's environment variables first, default to local if missing
        $this->host = getenv('DB_HOST') ?: "localhost";
        $this->port = getenv('DB_PORT') ?: "3306"; // Default MySQL port
        $this->db   = getenv('DB_DATABASE') ?: "sms";
        $this->user = getenv('DB_USER') ?: "root";
        $this->pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ""; // Default password for local development

        try {
            // Added port mapping and upgraded charset to utf8mb4 (standard for modern MySQL)
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";
            
            $this->conn = new PDO($dsn, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Forces PDO to return rows as associative arrays by default
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('DB Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}


