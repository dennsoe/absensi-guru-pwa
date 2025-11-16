<?php

/**
 * Absensi Controller
 */
class AbsensiController extends Controller {
    
    public function __construct() {
        $this->requireAuth(['guru', 'ketua_kelas']);
    }
    
    /**
     * Halaman Absen Masuk
     */
    public function masuk() {
        $guruId = $_SESSION['guru_id'] ?? null;
        
        if (!$guruId) {
            $this->jsonError('Data guru tidak ditemukan', 403);
        }
        
        $jadwalModel = $this->model('JadwalMengajar');
        $currentJadwal = $jadwalModel->getCurrentJadwal($guruId);
        
        $data = [
            'title' => 'Absen Masuk',
            'jadwal' => $currentJadwal
        ];
        
        $this->view('absensi/masuk', $data);
    }
    
    /**
     * Process Absen Masuk
     */
    public function prosesAbsenMasuk() {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request', 400);
        }
        
        $guruId = $_SESSION['guru_id'] ?? null;
        
        if (!$guruId) {
            $this->jsonError('Data guru tidak ditemukan', 403);
        }
        
        // Get data
        $postData = $this->isAjax() ? $this->getJsonBody() : $this->post();
        
        $jadwalId = $postData['jadwal_id'] ?? null;
        $metodeAbsensi = $postData['metode_absensi'] ?? null;
        $latitude = $postData['latitude'] ?? null;
        $longitude = $postData['longitude'] ?? null;
        
        // Validate required fields
        if (!$jadwalId || !$metodeAbsensi) {
            $this->jsonError('Data tidak lengkap', 400);
        }
        
        $absensiModel = $this->model('Absensi');
        
        try {
            // Check if already absen
            $tanggalHariIni = date('Y-m-d');
            if ($absensiModel->hasAbsenMasuk($jadwalId, $tanggalHariIni)) {
                $this->jsonError('Anda sudah melakukan absen masuk untuk jadwal ini');
            }
            
            // Prepare data
            $absensiData = [
                'jadwal_id' => $jadwalId,
                'guru_id' => $guruId,
                'tanggal' => $tanggalHariIni,
                'metode_absensi' => $metodeAbsensi,
                'created_by' => $_SESSION['user_id']
            ];
            
            // Handle GPS validation
            if ($latitude && $longitude) {
                $gpsValidation = $absensiModel->validateGPS($latitude, $longitude);
                
                $absensiData['latitude'] = $latitude;
                $absensiData['longitude'] = $longitude;
                $absensiData['validasi_gps'] = $gpsValidation['valid'];
                $absensiData['jarak_dari_sekolah'] = $gpsValidation['distance'];
                
                // Check if GPS required and not valid
                $gpsRequired = $this->getSetting('gps_required');
                if ($gpsRequired && !$gpsValidation['valid']) {
                    $this->jsonError('Lokasi Anda di luar jangkauan sekolah. Jarak: ' . $gpsValidation['distance'] . ' meter', 400);
                }
            }
            
            // Handle QR Code
            if ($metodeAbsensi === 'qr_code') {
                $qrData = $postData['qr_data'] ?? null;
                
                if (!$qrData) {
                    $this->jsonError('QR Code tidak valid', 400);
                }
                
                $qrModel = $this->model('QRCode');
                $qrValidation = $qrModel->validateQR($qrData);
                
                if (!$qrValidation['valid']) {
                    $this->jsonError($qrValidation['message'], 400);
                }
                
                $absensiData['qr_code_data'] = $qrData;
                
                // Mark QR as used (by ketua kelas if applicable)
                $ketuaKelasId = $_SESSION['role'] === 'ketua_kelas' ? $_SESSION['user_id'] : null;
                $qrModel->markAsUsed($qrValidation['qr_id'], $ketuaKelasId);
            }
            
            // Handle Selfie
            if ($metodeAbsensi === 'selfie' || isset($_FILES['foto_selfie'])) {
                if (isset($_FILES['foto_selfie'])) {
                    $upload = $this->uploadFile($_FILES['foto_selfie'], 'uploads/selfie', ['jpg', 'jpeg', 'png']);
                    
                    if ($upload['success']) {
                        $absensiData['foto_selfie'] = $upload['path'];
                    } else {
                        $this->jsonError($upload['message'], 400);
                    }
                }
            }
            
            // Validasi ketua kelas
            if (isset($postData['validasi_ketua_kelas'])) {
                $absensiData['validasi_ketua_kelas'] = true;
                $absensiData['ketua_kelas_user_id'] = $_SESSION['user_id'];
                $absensiData['waktu_validasi_ketua'] = date('Y-m-d H:i:s');
            }
            
            // Insert absensi
            $absensiId = $absensiModel->absenMasuk($absensiData);
            
            // Send notification (if enabled)
            $this->sendNotification($guruId, 'Absensi Masuk Berhasil', 'Anda telah berhasil melakukan absen masuk');
            
            $this->jsonSuccess('Absen masuk berhasil dicatat', [
                'absensi_id' => $absensiId,
                'jam_masuk' => date('H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
    
    /**
     * Halaman Absen Keluar
     */
    public function keluar() {
        $guruId = $_SESSION['guru_id'] ?? null;
        
        if (!$guruId) {
            $this->redirect('/dashboard');
        }
        
        // Get today's absensi
        $absensiModel = $this->model('Absensi');
        $tanggalHariIni = date('Y-m-d');
        $absensiHariIni = $absensiModel->getByGuruAndDate($guruId, $tanggalHariIni);
        
        $data = [
            'title' => 'Absen Keluar',
            'absensi_list' => $absensiHariIni
        ];
        
        $this->view('absensi/keluar', $data);
    }
    
    /**
     * Process Absen Keluar
     */
    public function prosesAbsenKeluar() {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request', 400);
        }
        
        $absensiId = $this->post('absensi_id');
        
        if (!$absensiId) {
            $this->jsonError('Data tidak lengkap', 400);
        }
        
        $absensiModel = $this->model('Absensi');
        
        try {
            $absensiModel->absenKeluar($absensiId);
            
            $this->jsonSuccess('Absen keluar berhasil dicatat', [
                'jam_keluar' => date('H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
    
    /**
     * Riwayat Absensi
     */
    public function riwayat() {
        $guruId = $_SESSION['guru_id'] ?? null;
        
        if (!$guruId) {
            $this->redirect('/dashboard');
        }
        
        $absensiModel = $this->model('Absensi');
        
        // Get filter
        $bulan = $this->get('bulan', date('m'));
        $tahun = $this->get('tahun', date('Y'));
        
        // Get data
        $rekap = $absensiModel->getRekapByGuru($guruId, $bulan, $tahun);
        $riwayat = $absensiModel->getRiwayatAbsensi($guruId, 100);
        
        $data = [
            'title' => 'Riwayat Absensi',
            'rekap' => $rekap,
            'riwayat' => $riwayat,
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        
        $this->view('absensi/riwayat', $data);
    }
    
    /**
     * Generate QR Code
     */
    public function generateQR() {
        if (!$this->isPost()) {
            $this->jsonError('Invalid request', 400);
        }
        
        $guruId = $_SESSION['guru_id'] ?? null;
        $jadwalId = $this->post('jadwal_id');
        
        if (!$guruId || !$jadwalId) {
            $this->jsonError('Data tidak lengkap', 400);
        }
        
        $qrModel = $this->model('QRCode');
        
        try {
            // Check if there's active QR
            $activeQR = $qrModel->getActiveQR($guruId, $jadwalId);
            
            if ($activeQR) {
                $this->jsonSuccess('QR Code masih aktif', [
                    'qr_id' => $activeQR['qr_id'],
                    'qr_data' => $activeQR['qr_data'],
                    'qr_image_path' => $activeQR['qr_image_path'],
                    'expired_at' => $activeQR['expired_at']
                ]);
            }
            
            // Generate new QR
            $expiryMinutes = $this->getSetting('qr_expiry_time') / 60;
            $qrData = $qrModel->generateQR($guruId, $jadwalId, $expiryMinutes);
            
            $this->jsonSuccess('QR Code berhasil dibuat', $qrData);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
    
    /**
     * Get Setting Value
     */
    private function getSetting($key, $default = null) {
        $db = Database::getInstance();
        $result = $db->fetchOne(
            "SELECT value FROM pengaturan_sistem WHERE `key` = :key",
            ['key' => $key]
        );
        
        return $result ? $result['value'] : $default;
    }
    
    /**
     * Send Notification
     */
    private function sendNotification($userId, $judul, $pesan, $kategori = 'absensi') {
        try {
            $db = Database::getInstance();
            $db->insert('notifikasi', [
                'user_id' => $userId,
                'judul' => $judul,
                'pesan' => $pesan,
                'kategori' => $kategori,
                'tipe' => 'info'
            ]);
        } catch (Exception $e) {
            // Silent fail
            error_log("Failed to send notification: " . $e->getMessage());
        }
    }
}