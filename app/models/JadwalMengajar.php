<?php

/**
 * Jadwal Mengajar Model
 */
class JadwalMengajar extends Model {
    protected $table = 'jadwal_mengajar';
    protected $primaryKey = 'jadwal_id';
    
    /**
     * Get Jadwal with Details
     */
    public function getJadwalWithDetails($jadwalId) {
        $sql = "SELECT j.*, 
                       g.nama as nama_guru, g.nip,
                       m.nama_mapel, m.kode_mapel,
                       k.nama_kelas, k.tingkat, k.jurusan
                FROM jadwal_mengajar j
                JOIN guru g ON j.guru_id = g.guru_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE j.jadwal_id = :jadwal_id";
        
        return $this->db->fetchOne($sql, ['jadwal_id' => $jadwalId]);
    }
    
    /**
     * Get Jadwal By Guru
     */
    public function getByGuru($guruId, $hari = null) {
        $sql = "SELECT j.*, 
                       m.nama_mapel, m.kode_mapel,
                       k.nama_kelas, k.tingkat
                FROM jadwal_mengajar j
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE j.guru_id = :guru_id 
                  AND j.status = 'aktif'";
        
        $params = ['guru_id' => $guruId];
        
        if ($hari) {
            $sql .= " AND j.hari = :hari";
            $params['hari'] = $hari;
        }
        
        $sql .= " ORDER BY j.hari, j.jam_mulai";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get Jadwal Hari Ini untuk Guru
     */
    public function getJadwalHariIni($guruId) {
        $hari = $this->getCurrentHari();
        return $this->getByGuru($guruId, $hari);
    }
    
    /**
     * Get Jadwal By Kelas
     */
    public function getByKelas($kelasId, $hari = null) {
        $sql = "SELECT j.*, 
                       g.nama as nama_guru,
                       m.nama_mapel, m.kode_mapel
                FROM jadwal_mengajar j
                JOIN guru g ON j.guru_id = g.guru_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                WHERE j.kelas_id = :kelas_id 
                  AND j.status = 'aktif'";
        
        $params = ['kelas_id' => $kelasId];
        
        if ($hari) {
            $sql .= " AND j.hari = :hari";
            $params['hari'] = $hari;
        }
        
        $sql .= " ORDER BY j.hari, j.jam_mulai";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get Jadwal Aktif By Tahun Ajaran & Semester
     */
    public function getByTahunSemester($tahunAjaran, $semester) {
        $sql = "SELECT j.*, 
                       g.nama as nama_guru,
                       m.nama_mapel,
                       k.nama_kelas
                FROM jadwal_mengajar j
                JOIN guru g ON j.guru_id = g.guru_id
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE j.tahun_ajaran = :tahun 
                  AND j.semester = :semester
                  AND j.status = 'aktif'
                ORDER BY j.hari, j.jam_mulai";
        
        return $this->db->fetchAll($sql, [
            'tahun' => $tahunAjaran,
            'semester' => $semester
        ]);
    }
    
    /**
     * Check Jadwal Bentrok
     */
    public function checkBentrok($guruId, $hari, $jamMulai, $jamSelesai, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM jadwal_mengajar 
                WHERE guru_id = :guru_id 
                  AND hari = :hari 
                  AND status = 'aktif'
                  AND (
                    (jam_mulai < :jam_selesai AND jam_selesai > :jam_mulai)
                  )";
        
        $params = [
            'guru_id' => $guruId,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai
        ];
        
        if ($excludeId) {
            $sql .= " AND jadwal_id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $count = $this->db->fetchColumn($sql, $params);
        return $count > 0;
    }
    
    /**
     * Get Current Jadwal (sedang berlangsung)
     */
    public function getCurrentJadwal($guruId) {
        $hari = $this->getCurrentHari();
        $waktuSekarang = date('H:i:s');
        
        $sql = "SELECT j.*, 
                       m.nama_mapel,
                       k.nama_kelas
                FROM jadwal_mengajar j
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE j.guru_id = :guru_id 
                  AND j.hari = :hari
                  AND j.jam_mulai <= :waktu
                  AND j.jam_selesai >= :waktu
                  AND j.status = 'aktif'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [
            'guru_id' => $guruId,
            'hari' => $hari,
            'waktu' => $waktuSekarang
        ]);
    }
    
    /**
     * Get Next Jadwal
     */
    public function getNextJadwal($guruId) {
        $hari = $this->getCurrentHari();
        $waktuSekarang = date('H:i:s');
        
        $sql = "SELECT j.*, 
                       m.nama_mapel,
                       k.nama_kelas
                FROM jadwal_mengajar j
                JOIN mata_pelajaran m ON j.mapel_id = m.mapel_id
                JOIN kelas k ON j.kelas_id = k.kelas_id
                WHERE j.guru_id = :guru_id 
                  AND j.hari = :hari
                  AND j.jam_mulai > :waktu
                  AND j.status = 'aktif'
                ORDER BY j.jam_mulai ASC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [
            'guru_id' => $guruId,
            'hari' => $hari,
            'waktu' => $waktuSekarang
        ]);
    }
    
    /**
     * Get Current Hari
     */
    private function getCurrentHari() {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        return $days[date('l')];
    }
    
    /**
     * Get Jadwal Statistics
     */
    public function getStatistics($guruId = null) {
        $sql = "SELECT 
                    COUNT(*) as total_jadwal,
                    COUNT(DISTINCT guru_id) as total_guru,
                    COUNT(DISTINCT kelas_id) as total_kelas,
                    COUNT(DISTINCT mapel_id) as total_mapel
                FROM jadwal_mengajar
                WHERE status = 'aktif'";
        
        $params = [];
        
        if ($guruId) {
            $sql .= " AND guru_id = :guru_id";
            $params['guru_id'] = $guruId;
        }
        
        return $this->db->fetchOne($sql, $params);
    }
}