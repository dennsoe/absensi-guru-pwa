<?php

/**
 * Database Class
 * PDO-based database connection dengan singleton pattern
 */
class Database {
    private static $instance = null;
    private $pdo = null;
    private $config = [];
    
    private function __construct() {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    /**
     * Get Database Instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Connect to Database
     */
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
            
        } catch (PDOException $e) {
            if ($this->config['show_errors']) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                error_log("Database Connection Failed: " . $e->getMessage());
                die("Koneksi database gagal. Silakan hubungi administrator.");
            }
        }
    }
    
    /**
     * Get PDO Connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prepare & Execute Query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Log query jika enabled
            if ($this->config['log_queries']) {
                $this->logQuery($sql, $params);
            }
            
            return $stmt;
        } catch (PDOException $e) {
            if ($this->config['show_errors']) {
                die("Query Error: " . $e->getMessage() . "<br>SQL: " . $sql);
            } else {
                error_log("Query Error: " . $e->getMessage() . " SQL: " . $sql);
                throw $e;
            }
        }
    }
    
    /**
     * Fetch All Rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch Single Row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch Single Column
     */
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert Data
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update Data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete Data
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin Transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Check if in Transaction
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Get Last Insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Log Query (Development Only)
     */
    private function logQuery($sql, $params) {
        $logFile = __DIR__ . '/../../logs/queries.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $paramsStr = json_encode($params);
        $log = "[{$timestamp}] {$sql} | Params: {$paramsStr}\n";
        
        file_put_contents($logFile, $log, FILE_APPEND);
    }
    
    /**
     * Prevent Cloning
     */
    private function __clone() {}
    
    /**
     * Prevent Unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}