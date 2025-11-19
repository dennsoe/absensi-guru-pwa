<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\GuruPiketController as AdminGuruPiketController;
use App\Http\Controllers\Admin\KetuaKelasController as AdminKetuaKelasController;
use App\Http\Controllers\Admin\KalenderLiburController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SuratPeringatanController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\IzinController as AdminIzinController;
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
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\GuruPiket\DashboardController as GuruPiketDashboardController;
use App\Http\Controllers\KetuaKelas\DashboardController as KetuaKelasDashboardController;

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

    // Fallback untuk logout GET (redirect ke dashboard dengan pesan)
    Route::get('/logout', function() {
        return redirect()->route('dashboard')->with('error', 'Logout harus menggunakan tombol Logout yang tersedia di menu.');
    });

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
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'getRealtimeStats'])->name('dashboard.stats');
        Route::get('/dashboard/live-guru-status', [AdminDashboardController::class, 'getLiveGuruStatus'])->name('dashboard.live-guru-status');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        // Guru Management (NEW - dari AdminController)
        Route::get('/guru', [AdminController::class, 'guru'])->name('guru');
        Route::get('/guru/create', [AdminController::class, 'createGuru'])->name('guru.create');
        Route::post('/guru', [AdminController::class, 'storeGuru'])->name('guru.store');
        Route::get('/guru/{id}', [AdminController::class, 'showGuru'])->name('guru.show');
        Route::get('/guru/{id}/edit', [AdminController::class, 'editGuru'])->name('guru.edit');
        Route::put('/guru/{id}', [AdminController::class, 'updateGuru'])->name('guru.update');
        Route::delete('/guru/{id}', [AdminController::class, 'destroyGuru'])->name('guru.destroy');

        // Kelas Management (NEW - dari AdminController)
        Route::get('/kelas', [AdminController::class, 'kelas'])->name('kelas');
        Route::get('/kelas/create', [AdminController::class, 'createKelas'])->name('kelas.create');
        Route::post('/kelas', [AdminController::class, 'storeKelas'])->name('kelas.store');
        Route::get('/kelas/{id}/edit', [AdminController::class, 'editKelas'])->name('kelas.edit');
        Route::put('/kelas/{id}', [AdminController::class, 'updateKelas'])->name('kelas.update');
        Route::delete('/kelas/{id}', [AdminController::class, 'destroyKelas'])->name('kelas.destroy');

        // Mata Pelajaran Management (NEW - dari AdminController)
        Route::get('/mata-pelajaran', [AdminController::class, 'mataPelajaran'])->name('mapel');
        Route::get('/mata-pelajaran/create', [AdminController::class, 'createMataPelajaran'])->name('mapel.create');
        Route::post('/mata-pelajaran', [AdminController::class, 'storeMataPelajaran'])->name('mapel.store');
        Route::get('/mata-pelajaran/{id}/edit', [AdminController::class, 'editMataPelajaran'])->name('mapel.edit');
        Route::put('/mata-pelajaran/{id}', [AdminController::class, 'updateMataPelajaran'])->name('mapel.update');
        Route::delete('/mata-pelajaran/{id}', [AdminController::class, 'destroyMataPelajaran'])->name('mapel.destroy');

        // System Settings (NEW)
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Activity Log (NEW)
        Route::get('/activity-log', [AdminController::class, 'activityLog'])->name('activity-log');

        // Jadwal Mengajar Management
        Route::get('/jadwal', [AdminJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [AdminJadwalController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal', [AdminJadwalController::class, 'store'])->name('jadwal.store');
        Route::get('/jadwal/{jadwal}/edit', [AdminJadwalController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{jadwal}', [AdminJadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{jadwal}', [AdminJadwalController::class, 'destroy'])->name('jadwal.destroy');

        // Absensi Management (Rekap/Monitoring)
        Route::get('/absensi', [AdminController::class, 'rekapAbsensi'])->name('absensi.index');

        // Laporan Absensi
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/per-guru', [AdminLaporanController::class, 'perGuru'])->name('laporan.per-guru');
        Route::get('/laporan/per-kelas', [AdminLaporanController::class, 'perKelas'])->name('laporan.per-kelas');
        Route::get('/laporan/keterlambatan', [AdminLaporanController::class, 'keterlambatan'])->name('laporan.keterlambatan');

        // Laporan PDF Exports
        Route::get('/laporan/export-pdf/per-guru', [AdminLaporanController::class, 'exportPdfPerGuru'])->name('laporan.export-pdf.per-guru');
        Route::get('/laporan/export-pdf/per-kelas', [AdminLaporanController::class, 'exportPdfPerKelas'])->name('laporan.export-pdf.per-kelas');
        Route::get('/laporan/export-pdf/rekap-bulanan', [AdminLaporanController::class, 'exportPdfRekapBulanan'])->name('laporan.export-pdf.rekap-bulanan');
        Route::get('/laporan/export-pdf/keterlambatan', [AdminLaporanController::class, 'exportPdfKeterlambatan'])->name('laporan.export-pdf.keterlambatan');

        // Laporan Excel Exports
        Route::get('/laporan/export-excel/per-guru', [AdminLaporanController::class, 'exportExcelPerGuru'])->name('laporan.export-excel.per-guru');
        Route::get('/laporan/export-excel/per-kelas', [AdminLaporanController::class, 'exportExcelPerKelas'])->name('laporan.export-excel.per-kelas');
        Route::get('/laporan/export-excel/rekap-bulanan', [AdminLaporanController::class, 'exportExcelRekapBulanan'])->name('laporan.export-excel.rekap-bulanan');
        Route::get('/laporan/export-excel/keterlambatan', [AdminLaporanController::class, 'exportExcelKeterlambatan'])->name('laporan.export-excel.keterlambatan');

        // Guru Piket Management
        Route::get('/guru-piket', [AdminGuruPiketController::class, 'index'])->name('guru-piket.index');
        Route::get('/guru-piket/assign', [AdminGuruPiketController::class, 'create'])->name('guru-piket.assign');
        Route::post('/guru-piket', [AdminGuruPiketController::class, 'store'])->name('guru-piket.store');
        Route::delete('/guru-piket/{id}', [AdminGuruPiketController::class, 'destroy'])->name('guru-piket.destroy');
        Route::patch('/guru-piket/{id}/status', [AdminGuruPiketController::class, 'updateStatus'])->name('guru-piket.update-status');

        // Ketua Kelas Management
        Route::get('/ketua-kelas', [AdminKetuaKelasController::class, 'index'])->name('ketua-kelas.index');
        Route::get('/ketua-kelas/assign', [AdminKetuaKelasController::class, 'create'])->name('ketua-kelas.assign');
        Route::post('/ketua-kelas', [AdminKetuaKelasController::class, 'store'])->name('ketua-kelas.store');
        Route::delete('/ketua-kelas/{id}', [AdminKetuaKelasController::class, 'destroy'])->name('ketua-kelas.remove');

        // Kalender Libur Management
        Route::get('/kalender-libur', [KalenderLiburController::class, 'index'])->name('kalender-libur.index');
        Route::post('/kalender-libur', [KalenderLiburController::class, 'store'])->name('kalender-libur.store');
        Route::put('/kalender-libur/{id}', [KalenderLiburController::class, 'update'])->name('kalender-libur.update');
        Route::delete('/kalender-libur/{id}', [KalenderLiburController::class, 'destroy'])->name('kalender-libur.destroy');

        // Backup Management
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup/trigger', [BackupController::class, 'trigger'])->name('backup.trigger');
        Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/backup/{filename}', [BackupController::class, 'delete'])->name('backup.delete');
        Route::post('/backup/cleanup', [BackupController::class, 'cleanup'])->name('backup.cleanup');

        // Surat Peringatan Management
        Route::get('/surat-peringatan', [SuratPeringatanController::class, 'index'])->name('surat-peringatan.index');
        Route::get('/surat-peringatan/generate', [SuratPeringatanController::class, 'generate'])->name('surat-peringatan.generate');
        Route::post('/surat-peringatan/process', [SuratPeringatanController::class, 'process'])->name('surat-peringatan.process');
        Route::get('/surat-peringatan/{id}/preview', [SuratPeringatanController::class, 'preview'])->name('surat-peringatan.preview');
        Route::get('/surat-peringatan/{id}/download', [SuratPeringatanController::class, 'download'])->name('surat-peringatan.download');
        Route::delete('/surat-peringatan/{id}', [SuratPeringatanController::class, 'destroy'])->name('surat-peringatan.destroy');

        // Broadcast Management
        Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('/broadcast/send', [BroadcastController::class, 'send'])->name('broadcast.send');
        Route::get('/broadcast/{id}', [BroadcastController::class, 'show'])->name('broadcast.show');
        Route::delete('/broadcast/{id}', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

        // Izin/Cuti Management for Admin
        Route::get('/izin', [AdminIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/{id}', [AdminIzinController::class, 'show'])->name('izin.show');
        Route::post('/izin/{id}/approve', [AdminIzinController::class, 'approve'])->name('izin.approve');
        Route::post('/izin/{id}/reject', [AdminIzinController::class, 'reject'])->name('izin.reject');
        Route::delete('/izin/{id}', [AdminIzinController::class, 'destroy'])->name('izin.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Guru Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:guru,ketua_kelas'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
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

        // Absensi Keluar (NEW)
        Route::get('/absensi/keluar', [GuruController::class, 'absensiKeluar'])->name('absensi.keluar');
        Route::post('/absensi/proses-keluar', [GuruController::class, 'prosesAbsensiKeluar'])->name('absensi.proses-keluar');

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
        Route::get('/dashboard', [GuruPiketDashboardController::class, 'index'])->name('dashboard');
        Route::get('/realtime-stats', [GuruPiketDashboardController::class, 'getRealtimeStats'])->name('realtime-stats');

        // Monitoring (AJAX endpoint)
        Route::get('/monitoring-absensi', [GuruPiketController::class, 'monitoringAbsensi'])->name('monitoring-absensi');

        // Manual Attendance Input (NEW)
        Route::get('/absensi-manual', [GuruPiketController::class, 'inputAbsensiManual'])->name('absensi-manual');
        Route::post('/absensi-manual', [GuruPiketController::class, 'storeAbsensiManual'])->name('absensi-manual.store');

        // Laporan (NEW)
        Route::get('/laporan-harian', [GuruPiketController::class, 'laporanHarian'])->name('laporan-harian');

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

        // Izin/Cuti Management
        Route::get('/izin', [AdminIzinController::class, 'index'])->name('izin.index');
        Route::get('/izin/{id}', [AdminIzinController::class, 'show'])->name('izin.show');
        Route::post('/izin/{id}/approve', [AdminIzinController::class, 'approve'])->name('izin.approve');
        Route::post('/izin/{id}/reject', [AdminIzinController::class, 'reject'])->name('izin.reject');
        Route::delete('/izin/{id}', [AdminIzinController::class, 'destroy'])->name('izin.destroy');

        // Laporan (NEW)
        Route::get('/laporan/kehadiran', [KepalaSekolahController::class, 'laporanKehadiran'])->name('laporan.kehadiran');
        Route::get('/laporan/kedisiplinan', [KepalaSekolahController::class, 'laporanKedisiplinan'])->name('laporan.kedisiplinan');

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

        // Jadwal Mengajar Management (NEW - dari KurikulumController)
        Route::get('/jadwal', [KurikulumController::class, 'jadwal'])->name('jadwal');
        Route::get('/jadwal/create', [KurikulumController::class, 'createJadwal'])->name('jadwal.create');
        Route::post('/jadwal', [KurikulumController::class, 'storeJadwal'])->name('jadwal.store');
        Route::get('/jadwal/{id}/edit', [KurikulumController::class, 'editJadwal'])->name('jadwal.edit');
        Route::put('/jadwal/{id}', [KurikulumController::class, 'updateJadwal'])->name('jadwal.update');
        Route::delete('/jadwal/{id}', [KurikulumController::class, 'destroyJadwal'])->name('jadwal.destroy');

        // Laporan Akademik (NEW)
        Route::get('/laporan-akademik', [KurikulumController::class, 'laporanAkademik'])->name('laporan-akademik');

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
        Route::get('/dashboard', [KetuaKelasDashboardController::class, 'index'])->name('dashboard');

        // Generate QR Code (NEW - dari KetuaKelasController)
        Route::get('/generate-qr', [KetuaKelasController::class, 'generateQr'])->name('generate-qr');
        Route::post('/qr-code', [KetuaKelasController::class, 'storeQrCode'])->name('qr-code.store');

        // Validasi Selfie (NEW)
        Route::get('/validasi-selfie', [KetuaKelasController::class, 'validasiSelfie'])->name('validasi-selfie');
        Route::post('/selfie/{id}/approve', [KetuaKelasController::class, 'approveSelfie'])->name('selfie.approve');
        Route::post('/selfie/{id}/reject', [KetuaKelasController::class, 'rejectSelfie'])->name('selfie.reject');

        // Riwayat & Statistik
        Route::get('/riwayat', [KetuaKelasController::class, 'riwayat'])->name('riwayat');
        Route::get('/statistik', [KetuaKelasController::class, 'statistik'])->name('statistik');
        Route::get('/jadwal', [KetuaKelasController::class, 'jadwal'])->name('jadwal');

        // Validasi Absensi (NEW)
        Route::get('/validasi', [KetuaKelasController::class, 'validasi'])->name('validasi');
        Route::post('/validasi/update', [KetuaKelasController::class, 'validasiUpdate'])->name('validasi.update');

        // Legacy routes (for backward compatibility)
        Route::get('/riwayat-scan', [KetuaKelasController::class, 'riwayatScan'])->name('riwayat-scan');
        Route::get('/statistik-scan', [KetuaKelasController::class, 'statistikScan'])->name('statistik-scan');
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
