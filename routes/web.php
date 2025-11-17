<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Guru\GuruController;
use App\Http\Controllers\GuruPiket\GuruPiketController;
use App\Http\Controllers\KepalaSekolah\KepalaSekolahController;
use App\Http\Controllers\Kurikulum\KurikulumController;
use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Laporan\LaporanController;
use App\Http\Controllers\Guru\AbsensiController as GuruAbsensiController;
use App\Http\Controllers\KetuaKelas\KetuaKelasController;
use App\Http\Controllers\GuruPiket\MonitoringController as GuruPiketMonitoringController;
use App\Http\Controllers\GuruPiket\LaporanPiketController;
use App\Http\Controllers\GuruPiket\KontakGuruController;
use App\Http\Controllers\KepalaSekolah\MonitoringController as KepalaSekolahMonitoringController;
use App\Http\Controllers\KepalaSekolah\ApprovalController as KepalaSekolahApprovalController;
use App\Http\Controllers\KepalaSekolah\LaporanEksekutifController;
use App\Http\Controllers\KepalaSekolah\AnalyticsController;
use App\Http\Controllers\Kurikulum\JadwalMengajarController;
use App\Http\Controllers\Kurikulum\GuruPenggantiController;
use App\Http\Controllers\Kurikulum\ApprovalController as KurikulumApprovalController;
use App\Http\Controllers\Kurikulum\LaporanAkademikController;
use App\Http\Controllers\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Guru\IzinController as GuruIzinController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;
use App\Http\Controllers\Jadwal\JadwalController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Belum Login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Semua yang sudah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes (All authenticated users)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function() {
            return redirect()->route('dashboard')->with('info', 'Fitur Profil sedang dalam pengembangan');
        })->name('index');

        Route::get('/edit', function() {
            return redirect()->route('dashboard')->with('info', 'Fitur Edit Profil sedang dalam pengembangan');
        })->name('edit');
    });

    // Notifikasi Routes (All authenticated users)
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', function() {
            return redirect()->route('dashboard')->with('info', 'Fitur Notifikasi sedang dalam pengembangan');
        })->name('index');

        Route::get('/{id}', function($id) {
            return redirect()->route('dashboard')->with('info', 'Fitur Notifikasi sedang dalam pengembangan');
        })->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin', 'log.activity'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Kelas Management
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/kelas/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kela}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kela}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kela}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        // Mata Pelajaran Management
        Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mapel.index');
        Route::get('/mata-pelajaran/create', [MataPelajaranController::class, 'create'])->name('mapel.create');
        Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mapel.store');
        Route::get('/mata-pelajaran/{mapel}/edit', [MataPelajaranController::class, 'edit'])->name('mapel.edit');
        Route::put('/mata-pelajaran/{mapel}', [MataPelajaranController::class, 'update'])->name('mapel.update');
        Route::delete('/mata-pelajaran/{mapel}', [MataPelajaranController::class, 'destroy'])->name('mapel.destroy');

        // Jadwal Mengajar Management
        Route::get('/jadwal', [AdminJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [AdminJadwalController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal', [AdminJadwalController::class, 'store'])->name('jadwal.store');
        Route::get('/jadwal/{jadwal}/edit', [AdminJadwalController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{jadwal}', [AdminJadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [AdminJadwalController::class, 'destroy'])->name('jadwal.destroy');

        // Absensi Management (placeholder)
        Route::get('/absensi', function() {
            return redirect()->route('admin.dashboard')->with('info', 'Fitur Monitoring Absensi sedang dalam pengembangan');
        })->name('absensi.index');

        // Laporan Absensi
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/per-guru', [AdminLaporanController::class, 'perGuru'])->name('laporan.per-guru');
        Route::get('/laporan/per-kelas', [AdminLaporanController::class, 'perKelas'])->name('laporan.per-kelas');

        // Settings (placeholder)
        Route::get('/settings', function() {
            return redirect()->route('admin.dashboard')->with('info', 'Fitur Pengaturan sedang dalam pengembangan');
        })->name('settings');
    });

    /*
    |--------------------------------------------------------------------------
    | Guru Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:guru,ketua_kelas'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
        Route::get('/absensi/riwayat', [GuruController::class, 'riwayatAbsensi'])->name('absensi.riwayat-list');
        Route::get('/absensi/{absensi}', [GuruController::class, 'detailAbsensi'])->name('absensi.detail');

        // Jadwal Personal
        Route::get('/jadwal', [GuruJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/today', [GuruJadwalController::class, 'today'])->name('jadwal.today');
        Route::get('/jadwal/list-json', [GuruJadwalController::class, 'listJson'])->name('jadwal.list');
        Route::get('/jadwal/{jadwal}', [GuruJadwalController::class, 'show'])->name('jadwal.show');

        // Absensi routes (guru scan QR dari ketua kelas)
        Route::get('/absensi/scan-qr', [GuruAbsensiController::class, 'scanQr'])->name('absensi.scan-qr');
        Route::get('/absensi/selfie', [GuruAbsensiController::class, 'selfie'])->name('absensi.selfie');
        Route::post('/absensi/proses-qr', [GuruAbsensiController::class, 'prosesAbsensiQr'])->name('absensi.proses-qr');
        Route::post('/absensi/proses-selfie', [GuruAbsensiController::class, 'prosesAbsensiSelfie'])->name('absensi.proses-selfie');
        Route::get('/absensi/riwayat-hari-ini', [GuruAbsensiController::class, 'riwayat'])->name('absensi.riwayat');

        // Izin/Cuti Management
        Route::get('/izin', [GuruIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/create', [GuruIzinController::class, 'create'])->name('izin.create');
        Route::post('/izin', [GuruIzinController::class, 'store'])->name('izin.store');
        Route::get('/izin/{izin}', [GuruIzinController::class, 'show'])->name('izin.show');
        Route::get('/izin/{izin}/edit', [GuruIzinController::class, 'edit'])->name('izin.edit');
        Route::put('/izin/{izin}', [GuruIzinController::class, 'update'])->name('izin.update');
        Route::delete('/izin/{izin}', [GuruIzinController::class, 'destroy'])->name('izin.destroy');

        // Profile Management
        Route::get('/profile', [GuruProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [GuruProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [GuruProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/change-password', [GuruProfileController::class, 'changePassword'])->name('profile.change-password');
        Route::put('/profile/change-password', [GuruProfileController::class, 'updatePassword'])->name('profile.update-password');
    });

    /*
    |--------------------------------------------------------------------------
    | Guru Piket Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:guru_piket', 'log.activity'])->prefix('piket')->name('guru-piket.')->group(function () {
        Route::get('/dashboard', [GuruPiketController::class, 'dashboard'])->name('dashboard');
        Route::get('/absensi-manual', [GuruPiketController::class, 'inputAbsensiManual'])->name('absensi-manual');
        Route::post('/absensi-manual', [GuruPiketController::class, 'storeAbsensiManual'])->name('absensi-manual.store');

        // Monitoring Absensi
        Route::get('/monitoring', [GuruPiketMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/refresh', [GuruPiketMonitoringController::class, 'refresh'])->name('monitoring.refresh');
        Route::get('/monitoring/detail/{guru}', [GuruPiketMonitoringController::class, 'detail'])->name('monitoring.detail');

        // Laporan Piket
        Route::get('/laporan', [LaporanPiketController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/mingguan', [LaporanPiketController::class, 'mingguan'])->name('laporan.mingguan');
        Route::get('/laporan/export-pdf', [LaporanPiketController::class, 'exportPdf'])->name('laporan.export-pdf');

        // Kontak Guru
        Route::get('/kontak-guru', [KontakGuruController::class, 'index'])->name('kontak-guru.index');
        Route::get('/kontak-guru/{guru}', [KontakGuruController::class, 'show'])->name('kontak-guru.show');
        Route::get('/kontak-guru/export', [KontakGuruController::class, 'export'])->name('kontak-guru.export');
    });

    /*
    |--------------------------------------------------------------------------
    | Kepala Sekolah Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:kepala_sekolah', 'log.activity'])->prefix('kepsek')->name('kepala-sekolah.')->group(function () {
        Route::get('/dashboard', [KepalaSekolahController::class, 'dashboard'])->name('dashboard');

        // Monitoring Eksekutif
        Route::get('/monitoring', [KepalaSekolahMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/realtime', [KepalaSekolahMonitoringController::class, 'realtime'])->name('monitoring.realtime');
        Route::get('/monitoring/per-kelas/{kelas}', [KepalaSekolahMonitoringController::class, 'perKelas'])->name('monitoring.per-kelas');

        // Approval Izin/Cuti
        Route::get('/approval', [KepalaSekolahApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/{izin}', [KepalaSekolahApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{izin}/approve', [KepalaSekolahApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{izin}/reject', [KepalaSekolahApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval/approve-multiple', [KepalaSekolahApprovalController::class, 'approveMultiple'])->name('approval.approve-multiple');

        // Laporan Eksekutif
        Route::get('/laporan', [LaporanEksekutifController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/bulanan', [LaporanEksekutifController::class, 'bulanan'])->name('laporan.bulanan');
        Route::get('/laporan/export-pdf', [LaporanEksekutifController::class, 'exportPdf'])->name('laporan.export-pdf');
        Route::get('/laporan/per-kelas/{kelas}', [LaporanEksekutifController::class, 'perKelas'])->name('laporan.per-kelas');

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/per-guru/{guru}', [AnalyticsController::class, 'perGuru'])->name('analytics.per-guru');

        // Legacy routes (for backward compatibility)
        Route::get('/izin-cuti', [KepalaSekolahController::class, 'izinCuti'])->name('izin-cuti');
        Route::post('/izin-cuti/{izin}/approve', [KepalaSekolahController::class, 'approveIzin'])->name('izin-cuti.approve');
        Route::post('/izin-cuti/{izin}/reject', [KepalaSekolahController::class, 'rejectIzin'])->name('izin-cuti.reject');
        Route::get('/laporan/kedisiplinan', [KepalaSekolahController::class, 'laporanKedisiplinan'])->name('laporan.kedisiplinan');
    });

    /*
    |--------------------------------------------------------------------------
    | Kurikulum Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:kurikulum', 'log.activity'])->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('/dashboard', [KurikulumController::class, 'dashboard'])->name('dashboard');

        // Jadwal Mengajar Management
        Route::get('/jadwal', [JadwalMengajarController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [JadwalMengajarController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal', [JadwalMengajarController::class, 'store'])->name('jadwal.store');
        Route::get('/jadwal/{jadwal}/edit', [JadwalMengajarController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{jadwal}', [JadwalMengajarController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [JadwalMengajarController::class, 'destroy'])->name('jadwal.destroy');

        // Guru Pengganti Management
        Route::get('/guru-pengganti', [GuruPenggantiController::class, 'index'])->name('guru-pengganti.index');
        Route::get('/guru-pengganti/create', [GuruPenggantiController::class, 'create'])->name('guru-pengganti.create');
        Route::post('/guru-pengganti', [GuruPenggantiController::class, 'store'])->name('guru-pengganti.store');
        Route::post('/guru-pengganti/{pengganti}/approve', [GuruPenggantiController::class, 'approve'])->name('guru-pengganti.approve');
        Route::post('/guru-pengganti/{pengganti}/reject', [GuruPenggantiController::class, 'reject'])->name('guru-pengganti.reject');
        Route::delete('/guru-pengganti/{pengganti}', [GuruPenggantiController::class, 'destroy'])->name('guru-pengganti.destroy');

        // Approval
        Route::get('/approval', [KurikulumApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/{pengganti}', [KurikulumApprovalController::class, 'show'])->name('approval.show');
        Route::post('/approval/{pengganti}/approve', [KurikulumApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{pengganti}/reject', [KurikulumApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval/jadwal/{jadwal}/approve', [KurikulumApprovalController::class, 'approveJadwal'])->name('approval.jadwal.approve');
        Route::post('/approval/jadwal/{jadwal}/reject', [KurikulumApprovalController::class, 'rejectJadwal'])->name('approval.jadwal.reject');

        // Laporan Akademik
        Route::get('/laporan', [LaporanAkademikController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/per-guru', [LaporanAkademikController::class, 'perGuru'])->name('laporan.per-guru');
        Route::get('/laporan/per-mapel', [LaporanAkademikController::class, 'perMapel'])->name('laporan.per-mapel');
        Route::get('/laporan/export-pdf', [LaporanAkademikController::class, 'exportPdf'])->name('laporan.export-pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Ketua Kelas Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:ketua_kelas'])->prefix('ketua-kelas')->name('ketua-kelas.')->group(function () {
        Route::get('/dashboard', function() {
            $data = [
                'total_scan_hari_ini' => 0,
                'scan_valid' => 0,
                'scan_invalid' => 0,
                'total_scan_minggu_ini' => 0,
                'riwayat_scan_hari_ini' => collect([]),
                'jadwal_kelas_hari_ini' => collect([]),
                'scan_valid_minggu' => 0,
                'scan_invalid_minggu' => 0,
                'tingkat_keberhasilan' => 0,
            ];
            return view('ketua-kelas.dashboard', $data);
        })->name('dashboard');

        // Generate QR (ketua kelas generate QR untuk discan guru)
        Route::get('/generate-qr', [KetuaKelasController::class, 'generateQr'])->name('generate-qr');
        Route::get('/riwayat-scan', [KetuaKelasController::class, 'riwayatScan'])->name('riwayat-scan');
        Route::get('/statistik-scan', [KetuaKelasController::class, 'statistikScan'])->name('statistik-scan');
        Route::get('/statistik', [KetuaKelasController::class, 'statistik'])->name('statistik');

        // Riwayat
        Route::get('/riwayat', [KetuaKelasController::class, 'riwayat'])->name('riwayat');

        // Jadwal
        Route::get('/jadwal', [KetuaKelasController::class, 'jadwal'])->name('jadwal');
    });

    /*
    |--------------------------------------------------------------------------
    | Absensi Routes (Semua yang sudah login bisa absen)
    |--------------------------------------------------------------------------
    */
    Route::middleware('absensi.time')->prefix('absensi')->name('absensi.')->group(function () {
        // QR Code
        Route::get('/scan-qr', [AbsensiController::class, 'scanQr'])->name('scan-qr');
        Route::post('/scan-qr', [AbsensiController::class, 'prosesAbsensiQr'])->name('scan-qr.proses');

        // Selfie
        Route::get('/selfie', [AbsensiController::class, 'selfie'])->name('selfie');
        Route::post('/selfie', [AbsensiController::class, 'prosesAbsensiSelfie'])->name('selfie.proses');
    });

    /*
    |--------------------------------------------------------------------------
    | Jadwal Routes (Semua bisa lihat jadwal)
    |--------------------------------------------------------------------------
    */
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/hari-ini', [JadwalController::class, 'hariIni'])->name('hari-ini');
        Route::get('/per-kelas', [JadwalController::class, 'perKelas'])->name('per-kelas');
        Route::get('/per-guru', [JadwalController::class, 'perGuru'])->name('per-guru');

        // QR Code Generation (Guru Piket only)
        Route::middleware(['role:guru_piket'])->group(function () {
            Route::post('/generate-qr', [JadwalController::class, 'generateQrCode'])->name('generate-qr');
            Route::post('/qr/{qrCode}/nonaktifkan', [JadwalController::class, 'nonaktifkanQrCode'])->name('qr.nonaktifkan');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Laporan Routes (Role tertentu)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,kepala_sekolah,kurikulum'])->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/export-pdf', [LaporanController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/detail-guru/{guru}', [LaporanController::class, 'detailGuru'])->name('detail-guru');
        Route::post('/simpan', [LaporanController::class, 'simpanLaporan'])->name('simpan');
    });
});
