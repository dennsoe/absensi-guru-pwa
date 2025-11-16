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

        // Jadwal (placeholder)
        Route::get('/jadwal', function() {
            return redirect()->route('guru.dashboard')->with('info', 'Fitur Jadwal sedang dalam pengembangan');
        })->name('jadwal.index');

        // Absensi routes (guru scan QR dari ketua kelas)
        Route::get('/absensi/scan-qr', [GuruAbsensiController::class, 'scanQr'])->name('absensi.scan-qr');
        Route::get('/absensi/selfie', [GuruAbsensiController::class, 'selfie'])->name('absensi.selfie');
        Route::post('/absensi/proses-qr', [GuruAbsensiController::class, 'prosesAbsensiQr'])->name('absensi.proses-qr');
        Route::post('/absensi/proses-selfie', [GuruAbsensiController::class, 'prosesAbsensiSelfie'])->name('absensi.proses-selfie');
        Route::get('/absensi/riwayat-hari-ini', [GuruAbsensiController::class, 'riwayat'])->name('absensi.riwayat');

        // Izin (placeholder)
        Route::get('/izin', function() {
            return redirect()->route('guru.dashboard')->with('info', 'Fitur Riwayat Izin sedang dalam pengembangan');
        })->name('izin.index');

        Route::get('/izin/create', function() {
            return redirect()->route('guru.dashboard')->with('info', 'Fitur Ajukan Izin sedang dalam pengembangan');
        })->name('izin.create');
    });

    /*
    |--------------------------------------------------------------------------
    | Guru Piket Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:guru_piket', 'log.activity'])->prefix('piket')->name('guru-piket.')->group(function () {
        Route::get('/dashboard', [GuruPiketController::class, 'dashboard'])->name('dashboard');
        Route::get('/monitoring', [GuruPiketController::class, 'monitoringAbsensi'])->name('monitoring');
        Route::get('/absensi-manual', [GuruPiketController::class, 'inputAbsensiManual'])->name('absensi-manual');
        Route::post('/absensi-manual', [GuruPiketController::class, 'storeAbsensiManual'])->name('absensi-manual.store');

        // Kontak Guru (placeholder)
        Route::get('/kontak-guru', function() {
            return redirect()->route('guru-piket.dashboard')->with('info', 'Fitur Kontak Guru sedang dalam pengembangan');
        })->name('kontak-guru');

        // Laporan (placeholder)
        Route::get('/laporan', function() {
            return redirect()->route('guru-piket.dashboard')->with('info', 'Fitur Laporan Piket sedang dalam pengembangan');
        })->name('laporan');
    });

    /*
    |--------------------------------------------------------------------------
    | Kepala Sekolah Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:kepala_sekolah', 'log.activity'])->prefix('kepsek')->name('kepala-sekolah.')->group(function () {
        Route::get('/dashboard', [KepalaSekolahController::class, 'dashboard'])->name('dashboard');

        // Monitoring (placeholder)
        Route::get('/monitoring', function() {
            return redirect()->route('kepala-sekolah.dashboard')->with('info', 'Fitur Monitoring sedang dalam pengembangan');
        })->name('monitoring');

        // Approval (placeholder)
        Route::get('/approval', function() {
            return redirect()->route('kepala-sekolah.dashboard')->with('info', 'Fitur Approval sedang dalam pengembangan');
        })->name('approval');

        // Analytics (placeholder)
        Route::get('/analytics', function() {
            return redirect()->route('kepala-sekolah.dashboard')->with('info', 'Fitur Analytics sedang dalam pengembangan');
        })->name('analytics');

        // Laporan (placeholder)
        Route::get('/laporan', function() {
            return redirect()->route('kepala-sekolah.dashboard')->with('info', 'Fitur Laporan sedang dalam pengembangan');
        })->name('laporan');

        // Approval Izin/Cuti
        Route::get('/izin-cuti', [KepalaSekolahController::class, 'izinCuti'])->name('izin-cuti');
        Route::post('/izin-cuti/{izin}/approve', [KepalaSekolahController::class, 'approveIzin'])->name('izin-cuti.approve');
        Route::post('/izin-cuti/{izin}/reject', [KepalaSekolahController::class, 'rejectIzin'])->name('izin-cuti.reject');

        // Laporan Kedisiplinan
        Route::get('/laporan/kedisiplinan', [KepalaSekolahController::class, 'laporanKedisiplinan'])->name('laporan.kedisiplinan');
    });

    /*
    |--------------------------------------------------------------------------
    | Kurikulum Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:kurikulum', 'log.activity'])->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('/dashboard', [KurikulumController::class, 'dashboard'])->name('dashboard');

        // Guru Pengganti (placeholder)
        Route::get('/guru-pengganti', function() {
            return redirect()->route('kurikulum.dashboard')->with('info', 'Fitur Guru Pengganti sedang dalam pengembangan');
        })->name('guru-pengganti');

        Route::get('/atur-pengganti/{jadwal}', function() {
            return redirect()->route('kurikulum.dashboard')->with('info', 'Fitur Atur Pengganti sedang dalam pengembangan');
        })->name('atur-pengganti');

        Route::get('/ubah-pengganti/{jadwal}', function() {
            return redirect()->route('kurikulum.dashboard')->with('info', 'Fitur Ubah Pengganti sedang dalam pengembangan');
        })->name('ubah-pengganti');

        // Cek Konflik (placeholder)
        Route::get('/cek-konflik', function() {
            return redirect()->route('kurikulum.dashboard')->with('info', 'Fitur Cek Konflik sedang dalam pengembangan');
        })->name('cek-konflik');

        // Laporan (placeholder)
        Route::get('/laporan', function() {
            return redirect()->route('kurikulum.dashboard')->with('info', 'Fitur Laporan sedang dalam pengembangan');
        })->name('laporan');

        // Jadwal Mengajar
        Route::get('/jadwal', [KurikulumController::class, 'jadwal'])->name('jadwal');
        Route::get('/jadwal', [KurikulumController::class, 'jadwal'])->name('jadwal.index');
        Route::get('/jadwal/create', [KurikulumController::class, 'createJadwal'])->name('jadwal.create');
        Route::post('/jadwal', [KurikulumController::class, 'storeJadwal'])->name('jadwal.store');
        Route::get('/jadwal/{jadwal}/edit', [KurikulumController::class, 'editJadwal'])->name('jadwal.edit');
        Route::put('/jadwal/{jadwal}', [KurikulumController::class, 'updateJadwal'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [KurikulumController::class, 'destroyJadwal'])->name('jadwal.destroy');
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
