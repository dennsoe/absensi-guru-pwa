<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // Real-time statistics
        $totalGuruAktif = Guru::count();
        $sudahAbsen = Absensi::whereDate('tanggal', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->distinct('guru_id')
            ->count('guru_id');
        $belumAbsen = $totalGuruAktif - $sudahAbsen;
        $guruIzin = Izin::whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('status', 'approved')
            ->distinct('guru_id')
            ->count('guru_id');

        // Jadwal hari ini
        $dayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek; // Convert Sunday (0) to 7
        $jadwalHariIni = Jadwal::with(['guru', 'kelas', 'mataPelajaran'])
            ->where('hari', $dayOfWeek)
            
            ->orderBy('jam_mulai')
            ->get();

        // Absensi terlambat hari ini
        $absensiTerlambat = Absensi::with('guru')
            ->whereDate('tanggal', $today)
            ->where('status', 'terlambat')
            ->orderBy('jam_masuk', 'desc')
            ->limit(10)
            ->get();

        // Guru yang belum absen (memiliki jadwal hari ini tapi belum absen)
        $guruBelumAbsen = Guru::with('user')
            
            ->whereHas('jadwals', function($query) use ($dayOfWeek) {
                $query->where('hari', $dayOfWeek)
                    ;
            })
            ->whereDoesntHave('absensis', function($query) use ($today) {
                $query->whereDate('tanggal', $today);
            })
            ->whereDoesntHave('izins', function($query) use ($today) {
                $query->whereDate('tanggal_mulai', '<=', $today)
                    ->whereDate('tanggal_selesai', '>=', $today)
                    ->where('status', 'approved');
            })
            ->limit(15)
            ->get();

        // Guru yang sedang izin hari ini
        $guruSedangIzin = Izin::with(['guru.user'])
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('status', 'approved')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Jadwal yang perlu pengganti (guru izin tapi belum ada pengganti)
        $jadwalPerluPengganti = Jadwal::with(['guru.user', 'kelas', 'mataPelajaran'])
            ->where('hari', $dayOfWeek)
            
            ->whereHas('guru.izins', function($query) use ($today) {
                $query->whereDate('tanggal_mulai', '<=', $today)
                    ->whereDate('tanggal_selesai', '>=', $today)
                    ->where('status', 'approved');
            })
            ->whereDoesntHave('guruPenggantiRelations', function($query) use ($today) {
                $query->whereDate('tanggal', $today);
            })
            ->orderBy('jam_mulai')
            ->get();

        // Aktivitas terbaru (absensi real-time)
        $aktivitasTerbaru = Absensi::with(['guru.user'])
            ->whereDate('tanggal', $today)
            ->orderBy('jam_masuk', 'desc')
            ->limit(20)
            ->get();

        return view('guru-piket.dashboard', compact(
            'totalGuruAktif',
            'sudahAbsen',
            'belumAbsen',
            'guruIzin',
            'jadwalHariIni',
            'absensiTerlambat',
            'guruBelumAbsen',
            'guruSedangIzin',
            'jadwalPerluPengganti',
            'aktivitasTerbaru'
        ));
    }

    /**
     * Get real-time stats (for AJAX refresh)
     */
    public function getRealtimeStats()
    {
        $today = Carbon::today();

        $totalGuruAktif = Guru::count();
        $sudahAbsen = Absensi::whereDate('tanggal', $today)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->distinct('guru_id')
            ->count('guru_id');
        $belumAbsen = $totalGuruAktif - $sudahAbsen;
        $guruIzin = Izin::whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('status', 'approved')
            ->distinct('guru_id')
            ->count('guru_id');

        return response()->json([
            'totalGuruAktif' => $totalGuruAktif,
            'sudahAbsen' => $sudahAbsen,
            'belumAbsen' => $belumAbsen,
            'guruIzin' => $guruIzin,
            'timestamp' => now()->format('H:i:s')
        ]);
    }
}
