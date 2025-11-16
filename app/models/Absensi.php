<?php

/**
 * Absensi Model
 */
class Absensi extends Model {
    protected $table = 'absensi';
    protected $primaryKey = 'absensi_id';
    
    /**
     * Get Absensi with Details
     */
    public function getAbsensiWithDetails($absensiId) {
        $sql = "SELECT a.*, 
                       g.nama as nama_guru, g.nip,
                       j.hari, j.jam_mulai, j.jam_selesai,
                       m.nama_mapel,
                       k.nama_kelas
                FROM absensi a
                JOIN guru g ON a.guru_id = g.guru_id
                JOIN jadwal_mengajar j ON a.jadwal_id = j.jadwal_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE a.absensi_id = :absensi_id";
        
        return $this->db->fetchOne($sql, ['absensi_id' => $absensiId]);
    }
    
    /**
     * Get Absensi By Guru & Tanggal
     */
    public function getByGuruAndDate($guruId, $tanggal) {
        $sql = "SELECT a.*, 
                       j.hari, j.jam_mulai, j.jam_selesai,
                       m.nama_mapel,
                       k.nama_kelas
                FROM absensi a
                JOIN jadwal_mengajar j ON a.jadwal_id = j.jadwal_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE a.guru_id = :guru_id 
                  AND a.tanggal = :tanggal
                ORDER BY j.jam_mulai ASC";
        
        return $this->db->fetchAll($sql, [
            'guru_id' => $guruId,
            'tanggal' => $tanggal
        ]);
    }
    
    /**
     * Get Absensi By Jadwal & Tanggal
     */
    public function getByJadwalAndDate($jadwalId, $tanggal) {
        return $this->findOneWhere(
            'jadwal_id = :jadwal_id AND tanggal = :tanggal',
            ['jadwal_id' => $jadwalId, 'tanggal' => $tanggal]
        );
    }
    
    /**
     * Check if Already Absen
     */
    public function hasAbsenMasuk($jadwalId, $tanggal) {
        $count = $this->count(
            'jadwal_id = :jadwal_id AND tanggal = :tanggal AND jam_masuk IS NOT NULL',
            ['jadwal_id' => $jadwalId, 'tanggal' => $tanggal]
        );
        
        return $count > 0;
    }
    
    /**
     * Absen Masuk
     */
    public function absenMasuk($data) {
        $required = [
            'jadwal_id', 'guru_id', 'tanggal', 'metode_absensi'
        ];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Field {$field} wajib diisi");
            }
        }
        
        // Check if already absen
        if ($this->hasAbsenMasuk($data['jadwal_id'], $data['tanggal'])) {
            throw new Exception("Sudah melakukan absen masuk untuk jadwal ini");
        }
        
        $data['jam_masuk'] = date('H:i:s');
        
        // Tentukan status kehadiran berdasarkan waktu
        $jadwal = $this->db->fetchOne(
            "SELECT jam_mulai FROM jadwal_mengajar WHERE jadwal_id = :id",
            ['id' => $data['jadwal_id']]
        );
        
        if ($jadwal) {
            $batasTerlambat = $this->getBatasTerlambat();
            $jamMasuk = strtotime($data['jam_masuk']);
            $jamJadwal = strtotime($jadwal['jam_mulai']);
            $selisih = ($jamMasuk - $jamJadwal) / 60; // dalam menit
            
            if ($selisih > $batasTerlambat) {
                $data['status_kehadiran'] = 'terlambat';
            } else {
                $data['status_kehadiran'] = 'hadir';
            }
        }
        
        return $this->create($data);
    }
    
    /**
     * Absen Keluar
     */
    public function absenKeluar($absensiId) {
        $absensi = $this->findById($absensiId);
        
        if (!$absensi) {
            throw new Exception("Data absensi tidak ditemukan");
        }
        
        if ($absensi['jam_keluar']) {
            throw new Exception("Sudah melakukan absen keluar");
        }
        
        return $this->update($absensiId, [
            'jam_keluar' => date('H:i:s')
        ]);
    }
    
    /**
     * Get Rekap Absensi Guru
     */
    public function getRekapByGuru($guruId, $bulan = null, $tahun = null) {
        $bulan = $bulan ?? date('m');
        $tahun = $tahun ?? date('Y');
        
        $sql = "SELECT 
                    COUNT(*) as total_absensi,
                    SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status_kehadiran = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status_kehadiran = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status_kehadiran = 'alpha' THEN 1 ELSE 0 END) as alpha,
                    SUM(CASE WHEN status_kehadiran = 'dinas' THEN 1 ELSE 0 END) as dinas,
                    SUM(CASE WHEN status_kehadiran = 'cuti' THEN 1 ELSE 0 END) as cuti
                FROM absensi
                WHERE guru_id = :guru_id 
                  AND MONTH(tanggal) = :bulan 
                  AND YEAR(tanggal) = :tahun";
        
        return $this->db->fetchOne($sql, [
            'guru_id' => $guruId,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
    }
    
    /**
     * Get Riwayat Absensi
     */
    public function getRiwayatAbsensi($guruId, $limit = 30) {
        $sql = "SELECT a.*, 
                       j.hari, j.jam_mulai, j.jam_selesai,
                       m.nama_mapel,
                       k.nama_kelas
                FROM absensi a
                JOIN jadwal_mengajar j ON a.jadwal_id = j.jadwal_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE a.guru_id = :guru_id
                ORDER BY a.tanggal DESC, a.jam_masuk DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, ['guru_id' => $guruId]);
    }
    
    /**
     * Get Batas Terlambat dari Settings
     */
    private function getBatasTerlambat() {
        $settings = $this->db->fetchOne(
            "SELECT value FROM pengaturan_sistem WHERE `key` = 'batas_terlambat'",
            []
        );
        
        return $settings ? (int) $settings['value'] : 15; // default 15 menit
    }
    
    /**
     * Validate GPS
     */
    public function validateGPS($latitude, $longitude) {
        // Get school coordinates from settings
        $latSchool = $this->db->fetchColumn(
            "SELECT value FROM pengaturan_sistem WHERE `key` = 'gps_latitude'"
        );
        $lonSchool = $this->db->fetchColumn(
            "SELECT value FROM pengaturan_sistem WHERE `key` = 'gps_longitude'"
        );
        $radius = $this->db->fetchColumn(
            "SELECT value FROM pengaturan_sistem WHERE `key` = 'gps_radius'"
        );
        
        if (!$latSchool || !$lonSchool) {
            return ['valid' => false, 'distance' => null];
        }
        
        // Calculate distance using Haversine formula
        $earthRadius = 6371000; // in meters
        
        $latFrom = deg2rad($latSchool);
        $lonFrom = deg2rad($lonSchool);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;
        
        $valid = $distance <= $radius;
        
        return [
            'valid' => $valid,
            'distance' => round($distance)
        ];
    }
}