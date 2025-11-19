<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar, IzinCuti};
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Monitoring Real-time Absensi
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', today()->toDateString());
        $hari_ini = Carbon::parse($tanggal)->locale('id')->dayName;

        // Jadwal hari ini
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                        
                                        ->with(['guru', 'kelas', 'mataPelajaran'])
                                        ->orderBy('jam_mulai')
                                        ->get();

        // Ambil semua absensi untuk tanggal tersebut
        $absensi = Absensi::whereDate('tanggal', $tanggal)
                          ->with(['guru', 'jadwal.kelas', 'jadwal.mataPelajaran'])
                          ->orderBy('jam_masuk', 'desc')
                          ->get();

        // Statistik
        $stats = [
            'total_jadwal' => $jadwal_hari_ini->count(),
            'sudah_absen' => $absensi->count(),
            'belum_absen' => $jadwal_hari_ini->count() - $absensi->count(),
            'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensi->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
        ];

        // Guru yang belum absen
        $guru_belum_absen = $jadwal_hari_ini->filter(function($jadwal) use ($absensi) {
            return !$absensi->pluck('guru_id')->contains($jadwal->guru_id);
        })->unique('guru_id');

        return view('guru-piket.monitoring.index', compact(
            'jadwal_hari_ini',
            'absensi',
            'stats',
            'guru_belum_absen',
            'tanggal'
        ));
    }

    /**
     * Detail monitoring per guru
     */
    public function detail($guruId)
    {
        $guru = Guru::findOrFail($guruId);

        $absensi_bulan_ini = Absensi::where('guru_id', $guruId)
                                    ->whereMonth('tanggal', now()->month)
                                    ->whereYear('tanggal', now()->year)
                                    ->with(['jadwal.kelas', 'jadwal.mataPelajaran'])
                                    ->orderBy('tanggal', 'desc')
                                    ->get();

        $stats = [
            'total' => $absensi_bulan_ini->count(),
            'hadir' => $absensi_bulan_ini->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi_bulan_ini->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensi_bulan_ini->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => $absensi_bulan_ini->where('status_kehadiran', 'alpha')->count(),
        ];

        return view('guru-piket.monitoring.detail', compact('guru', 'absensi_bulan_ini', 'stats'));
    }

    /**
     * Refresh monitoring (AJAX)
     */
    public function refresh()
    {
        $tanggal = today()->toDateString();
        $hari_ini = Carbon::parse($tanggal)->locale('id')->dayName();

        $absensi = Absensi::whereDate('tanggal', $tanggal)
                          ->with(['guru', 'jadwal.kelas', 'jadwal.mataPelajaran'])
                          ->orderBy('jam_masuk', 'desc')
                          ->get();

        $stats = [
            'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensi->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
        ];

        return response()->json([
            'success' => true,
            'absensi' => $absensi,
            'stats' => $stats,
            'timestamp' => now()->format('H:i:s')
        ]);
    }
}
