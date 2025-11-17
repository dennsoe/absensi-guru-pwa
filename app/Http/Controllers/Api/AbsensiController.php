<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar};
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /**
     * Check Absensi Status for Today
     */
    public function checkStatus(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->first();

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $tanggal = $request->get('tanggal', today()->toDateString());
        $jadwal_id = $request->get('jadwal_id');

        $query = Absensi::where('guru_id', $guru->id)
                       ->whereDate('tanggal', $tanggal);

        if ($jadwal_id) {
            $query->where('jadwal_id', $jadwal_id);
        }

        $absensi = $query->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                        ->orderBy('jam_absen', 'desc')
                        ->get();

        return response()->json([
            'success' => true,
            'tanggal' => $tanggal,
            'total_absensi' => $absensi->count(),
            'data' => $absensi,
        ]);
    }

    /**
     * Get Today's Absensi
     */
    public function today()
    {
        $guru = Guru::where('user_id', Auth::id())->first();

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $hari = ucfirst(now()->locale('id')->dayName);
        $tanggal = today()->toDateString();

        // Jadwal hari ini
        $jadwal_hari_ini = JadwalMengajar::with(['kelas', 'mataPelajaran'])
                                         ->where('guru_id', $guru->id)
                                         ->where('hari', $hari)
                                         ->where('status', 'aktif')
                                         ->orderBy('jam_mulai')
                                         ->get();

        // Absensi hari ini
        $absensi_hari_ini = Absensi::where('guru_id', $guru->id)
                                   ->whereDate('tanggal', $tanggal)
                                   ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                                   ->get();

        // Check which jadwal sudah/belum absen
        $jadwal_data = $jadwal_hari_ini->map(function($jadwal) use ($absensi_hari_ini) {
            $absensi = $absensi_hari_ini->firstWhere('jadwal_id', $jadwal->id);

            return [
                'jadwal_id' => $jadwal->id,
                'kelas' => $jadwal->kelas->nama_kelas,
                'mapel' => $jadwal->mataPelajaran->nama_mapel,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'ruangan' => $jadwal->ruangan,
                'sudah_absen' => $absensi ? true : false,
                'status_absen' => $absensi->status ?? null,
                'jam_absen' => $absensi->jam_absen ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'hari' => ucfirst($hari),
            'tanggal' => $tanggal,
            'total_jadwal' => $jadwal_hari_ini->count(),
            'sudah_absen' => $absensi_hari_ini->count(),
            'belum_absen' => $jadwal_hari_ini->count() - $absensi_hari_ini->count(),
            'jadwal' => $jadwal_data,
        ]);
    }

    /**
     * Get Absensi History
     */
    public function history(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->first();

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Guru not found',
            ], 404);
        }

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $limit = $request->get('limit', 20);

        $absensi = Absensi::where('guru_id', $guru->id)
                         ->whereMonth('tanggal', $bulan)
                         ->whereYear('tanggal', $tahun)
                         ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                         ->orderBy('tanggal', 'desc')
                         ->orderBy('jam_absen', 'desc')
                         ->paginate($limit);

        // Statistics
        $stats = Absensi::where('guru_id', $guru->id)
                       ->whereMonth('tanggal', $bulan)
                       ->whereYear('tanggal', $tahun)
                       ->selectRaw('
                           COUNT(*) as total,
                           SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
                           SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as terlambat,
                           SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin,
                           SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha
                       ')
                       ->first();

        return response()->json([
            'success' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'statistics' => $stats,
            'data' => $absensi->items(),
            'pagination' => [
                'current_page' => $absensi->currentPage(),
                'total_pages' => $absensi->lastPage(),
                'per_page' => $absensi->perPage(),
                'total' => $absensi->total(),
            ],
        ]);
    }
}
