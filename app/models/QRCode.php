<?php

/**
 * QR Code Model
 */
class QRCode extends Model {
    protected $table = 'qr_codes';
    protected $primaryKey = 'qr_id';
    
    /**
     * Generate QR Code untuk Jadwal
     */
    public function generateQR($guruId, $jadwalId, $expiryMinutes = 5) {
        // Generate unique QR data
        $qrData = $this->generateQRData($guruId, $jadwalId);
        
        // Set expiry time
        $expiredAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));
        
        // Create QR record
        $qrId = $this->create([
            'guru_id' => $guruId,
            'jadwal_id' => $jadwalId,
            'qr_data' => $qrData,
            'expired_at' => $expiredAt,
            'is_used' => false
        ]);
        
        // Generate QR image
        $imagePath = $this->generateQRImage($qrData, $qrId);
        
        // Update with image path
        if ($imagePath) {
            $this->update($qrId, ['qr_image_path' => $imagePath]);
        }
        
        return [
            'qr_id' => $qrId,
            'qr_data' => $qrData,
            'qr_image_path' => $imagePath,
            'expired_at' => $expiredAt
        ];
    }
    
    /**
     * Generate QR Data String
     */
    private function generateQRData($guruId, $jadwalId) {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $data = "{$guruId}|{$jadwalId}|{$timestamp}|{$random}";
        
        return hash('sha256', $data);
    }
    
    /**
     * Generate QR Image using external API or library
     */
    private function generateQRImage($qrData, $qrId) {
        // Option 1: Using Google Charts API
        $size = 300;
        $qrImageURL = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($qrData);
        
        // Download and save image
        $imageData = @file_get_contents($qrImageURL);
        
        if ($imageData) {
            $uploadDir = __DIR__ . '/../../public/uploads/qrcodes/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = "qr_{$qrId}_" . time() . ".png";
            $filepath = $uploadDir . $filename;
            
            if (file_put_contents($filepath, $imageData)) {
                return "/uploads/qrcodes/{$filename}";
            }
        }
        
        return null;
    }
    
    /**
     * Validate QR Code
     */
    public function validateQR($qrData) {
        $qr = $this->findBy('qr_data', $qrData, 1);
        
        if (!$qr) {
            return ['valid' => false, 'message' => 'QR Code tidak valid'];
        }
        
        // Check if already used
        if ($qr['is_used']) {
            return ['valid' => false, 'message' => 'QR Code sudah digunakan'];
        }
        
        // Check if expired
        if (strtotime($qr['expired_at']) < time()) {
            return ['valid' => false, 'message' => 'QR Code sudah kadaluarsa'];
        }
        
        return [
            'valid' => true,
            'qr_id' => $qr['qr_id'],
            'guru_id' => $qr['guru_id'],
            'jadwal_id' => $qr['jadwal_id']
        ];
    }
    
    /**
     * Mark QR as Used
     */
    public function markAsUsed($qrId, $usedByKetuaKelas = null) {
        return $this->update($qrId, [
            'is_used' => true,
            'used_at' => date('Y-m-d H:i:s'),
            'used_by_ketua_kelas' => $usedByKetuaKelas
        ]);
    }
    
    /**
     * Get Active QR by Guru & Jadwal
     */
    public function getActiveQR($guruId, $jadwalId) {
        $sql = "SELECT * FROM qr_codes 
                WHERE guru_id = :guru_id 
                  AND jadwal_id = :jadwal_id
                  AND is_used = 0
                  AND expired_at > NOW()
                ORDER BY created_at DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [
            'guru_id' => $guruId,
            'jadwal_id' => $jadwalId
        ]);
    }
    
    /**
     * Clean Expired QR Codes
     */
    public function cleanExpiredQR() {
        $sql = "DELETE FROM qr_codes 
                WHERE expired_at < DATE_SUB(NOW(), INTERVAL 1 DAY)";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get QR History by Guru
     */
    public function getHistoryByGuru($guruId, $limit = 10) {
        $sql = "SELECT q.*, 
                       j.hari, j.jam_mulai,
                       m.nama_mapel,
                       k.nama_kelas,
                       u.username as used_by_username
                FROM qr_codes q
                JOIN jadwal_mengajar j ON q.jadwal_id = j.jadwal_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                LEFT JOIN users u ON q.used_by_ketua_kelas = u.user_id
                WHERE q.guru_id = :guru_id
                ORDER BY q.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['guru_id' => $guruId]);
    }
}