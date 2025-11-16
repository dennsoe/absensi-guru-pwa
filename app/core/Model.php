<?php

/**
 * Base Model Class
 * Semua model extends dari class ini
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find All Records
     */
    public function findAll($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Find By ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Find By Condition
     */
    public function findBy($column, $value, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $limit === 1 
            ? $this->db->fetchOne($sql, ['value' => $value])
            : $this->db->fetchAll($sql, ['value' => $value]);
    }
    
    /**
     * Find Where
     */
    public function findWhere($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Find One Where
     */
    public function findOneWhere($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT 1";
        return $this->db->fetchOne($sql, $params);
    }
    
    /**
     * Create Record
     */
    public function create($data) {
        // Auto add timestamps
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update Record
     */
    public function update($id, $data) {
        // Auto update timestamp
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $where = "{$this->primaryKey} = :id";
        return $this->db->update($this->table, $data, $where, ['id' => $id]);
    }
    
    /**
     * Update Where
     */
    public function updateWhere($data, $conditions, $params = []) {
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->update($this->table, $data, $conditions, $params);
    }
    
    /**
     * Delete Record
     */
    public function delete($id) {
        $where = "{$this->primaryKey} = :id";
        return $this->db->delete($this->table, $where, ['id' => $id]);
    }
    
    /**
     * Delete Where
     */
    public function deleteWhere($conditions, $params = []) {
        return $this->db->delete($this->table, $conditions, $params);
    }
    
    /**
     * Count Records
     */
    public function count($conditions = null, $params = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        
        return (int) $this->db->fetchColumn($sql, $params);
    }
    
    /**
     * Check if Exists
     */
    public function exists($column, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$column} = :value";
        $params = ['value' => $value];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        
        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }
    
    /**
     * Get Table Name
     */
    public function getTable() {
        return $this->table;
    }
    
    /**
     * Begin Transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->db->rollback();
    }
}