<?php

/**
 * Dashboard Controller
 */
class DashboardController extends Controller {
    
    public function __construct() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    /**
     * Dashboard Index
     */
    public function index() {
        $role = $_SESSION['role'];
        
        // Route to role-specific dashboard
        switch ($role) {
            case 'guru':
                $this->guruDashboard();
                break;
            case 'admin':
                $this->adminDashboard();
                break;
            case 'kepala_sekolah':
                $this->kepalaDashboard();
                break;
            case 'ketua_kelas':
                $this->ketuaKelasDashboard();
                break;
            default:
                $this->defaultDashboard();
        }
    }
    
    /**
     * Dashboard Guru
     */
    private function guruDashboard() {
        $guruId = $_SESSION['guru_id'] ?? null;
        
        if (!$guruId) {
            $_SESSION['error'] = 'Data guru tidak ditemukan';
            $this->redirect('/login');
        }
        
        // Get data
        $jadwalModel = $this->model('JadwalMengajar');
        $absensiModel = $this->model('Absensi');
        
        $jadwalHariIni = $jadwalModel->getJadwalHariIni($guruId);
        $currentJadwal = $jadwalModel->getCurrentJadwal($guruId);
        $nextJadwal = $jadwalModel->getNextJadwal($guruId);
        
        $rekapBulanIni = $absensiModel->getRekapByGuru($guruId);
        $riwayatAbsensi = $absensiModel->getRiwayatAbsensi($guruId, 5);
        
        // Calculate attendance percentage
        $totalAbsensi = $rekapBulanIni['total_absensi'] ?? 0;
        $hadir = ($rekapBulanIni['hadir'] ?? 0) + ($rekapBulanIni['terlambat'] ?? 0);
        $persenKehadiran = $totalAbsensi > 0 ? round(($hadir / $totalAbsensi) * 100, 1) : 0;
        
        $data = [
            'title' => 'Dashboard Guru',
            'jadwal_hari_ini' => $jadwalHariIni,
            'current_jadwal' => $currentJadwal,
            'next_jadwal' => $nextJadwal,
            'rekap' => $rekapBulanIni,
            'persen_kehadiran' => $persenKehadiran,
            'riwayat' => $riwayatAbsensi
        ];
        
        $this->view('dashboard/guru', $data);
    }
    
    /**
     * Dashboard Admin
     */
    private function adminDashboard() {
        $guruModel = $this->model('Guru');
        $jadwalModel = $this->model('JadwalMengajar');
        $absensiModel = $this->model('Absensi');
        
        // Get statistics
        $statsGuru = $guruModel->getStatistics();
        $statsJadwal = $jadwalModel->getStatistics();
        
        // Today's attendance summary
        $tanggalHariIni = date('Y-m-d');
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status_kehadiran = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN status_kehadiran = 'alpha' THEN 1 ELSE 0 END) as alpha
                FROM absensi
                WHERE tanggal = :tanggal";
        
        $db = Database::getInstance();
        $absensiHariIni = $db->fetchOne($sql, ['tanggal' => $tanggalHariIni]);
        
        $data = [
            'title' => 'Dashboard Admin',
            'stats_guru' => $statsGuru,
            'stats_jadwal' => $statsJadwal,
            'absensi_hari_ini' => $absensiHariIni
        ];
        
        $this->view('dashboard/admin', $data);
    }
    
    /**
     * Dashboard Kepala Sekolah
     */
    private function kepalaDashboard() {
        // Similar to admin but with different focus
        $this->adminDashboard();
    }
    
    /**
     * Dashboard Ketua Kelas
     */
    private function ketuaKelasDashboard() {
        $userId = $_SESSION['user_id'];
        
        // Get kelas info
        $db = Database::getInstance();
        $kelas = $db->fetchOne(
            "SELECT * FROM kelas WHERE ketua_kelas_user_id = :user_id",
            ['user_id' => $userId]
        );
        
        if (!$kelas) {
            $_SESSION['error'] = 'Data kelas tidak ditemukan';
            $this->redirect('/dashboard');
        }
        
        // Get jadwal kelas hari ini
        $jadwalModel = $this->model('JadwalMengajar');
        $hari = $this->getCurrentHari();
        $jadwalHariIni = $jadwalModel->getByKelas($kelas['kelas_id'], $hari);
        
        $data = [
            'title' => 'Dashboard Ketua Kelas',
            'kelas' => $kelas,
            'jadwal_hari_ini' => $jadwalHariIni
        ];
        
        $this->view('dashboard/ketua_kelas', $data);
    }
    
    /**
     * Default Dashboard
     */
    private function defaultDashboard() {
        $data = [
            'title' => 'Dashboard',
            'message' => 'Selamat datang di Sistem Absensi Guru'
        ];
        
        $this->view('dashboard/default', $data);
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
}