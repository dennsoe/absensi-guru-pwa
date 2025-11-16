<?php

/**
 * Guru Model
 */
class Guru extends Model {
    protected $table = 'guru';
    protected $primaryKey = 'guru_id';
    
    /**
     * Get Guru with User Info
     */
    public function getGuruWithUser($guruId) {
        $sql = "SELECT g.*, u.username, u.role, u.status, u.last_login 
                FROM guru g 
                JOIN users u ON g.user_id = u.user_id 
                WHERE g.guru_id = :guru_id";
        
        return $this->db->fetchOne($sql, ['guru_id' => $guruId]);
    }
    
    /**
     * Get All Guru with User Info
     */
    public function getAllGuruWithUser() {
        $sql = "SELECT g.*, u.username, u.role, u.status, u.last_login 
                FROM guru g 
                JOIN users u ON g.user_id = u.user_id 
                ORDER BY g.nama ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Find By NIP
     */
    public function findByNIP($nip) {
        return $this->findBy('nip', $nip, 1);
    }
    
    /**
     * Find By User ID
     */
    public function findByUserId($userId) {
        return $this->findBy('user_id', $userId, 1);
    }
    
    /**
     * Get Guru By Status Kepegawaian
     */
    public function getByStatusKepegawaian($status) {
        return $this->findWhere('status_kepegawaian = :status', ['status' => $status]);
    }
    
    /**
     * Search Guru
     */
    public function searchGuru($keyword) {
        $sql = "SELECT g.*, u.username, u.status 
                FROM guru g 
                JOIN users u ON g.user_id = u.user_id 
                WHERE g.nama LIKE :keyword 
                   OR g.nip LIKE :keyword 
                   OR g.email LIKE :keyword
                ORDER BY g.nama ASC";
        
        return $this->db->fetchAll($sql, ['keyword' => "%{$keyword}%"]);
    }
    
    /**
     * Get Guru Statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_kepegawaian = 'PNS' THEN 1 ELSE 0 END) as pns,
                    SUM(CASE WHEN status_kepegawaian = 'PPPK' THEN 1 ELSE 0 END) as pppk,
                    SUM(CASE WHEN status_kepegawaian = 'Honorer' THEN 1 ELSE 0 END) as honorer,
                    SUM(CASE WHEN status_kepegawaian = 'GTT' THEN 1 ELSE 0 END) as gtt,
                    SUM(CASE WHEN status_kepegawaian = 'GTY' THEN 1 ELSE 0 END) as gty
                FROM guru";
        
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Check NIP Exists
     */
    public function nipExists($nip, $excludeId = null) {
        return $this->exists('nip', $nip, $excludeId);
    }
}