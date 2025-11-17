<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru};
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * Jadwal Mengajar Guru (Personal)
     */
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $hari = $request->get('hari');
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');
        $semester = $request->get('semester', 'Ganjil');

        $jadwal = JadwalMengajar::with(['kelas', 'mataPelajaran'])
                                ->where('guru_id', $guru->id)
                                ->where('tahun_ajaran', $tahun_ajaran)
                                ->where('semester', $semester)
                                ->where('status', 'aktif')
                                ->when($hari, fn($q) => $q->where('hari', ucfirst($hari)))
                                ->orderBy('hari')
                                ->orderBy('jam_mulai')
                                ->get();

        // Group by hari for better display
        $jadwal_grouped = $jadwal->groupBy('hari');

        // Statistics
        $total_jam_perminggu = $jadwal->sum(function($j) {
            $mulai = \Carbon\Carbon::parse($j->jam_mulai);
            $selesai = \Carbon\Carbon::parse($j->jam_selesai);
            return $mulai->diffInHours($selesai);
        });

        $total_kelas = $jadwal->pluck('kelas_id')->unique()->count();
        $total_mapel = $jadwal->pluck('mapel_id')->unique()->count();

        return view('guru.jadwal.index', compact(
            'jadwal',
            'jadwal_grouped',
            'hari',
            'tahun_ajaran',
            'semester',
            'total_jam_perminggu',
            'total_kelas',
            'total_mapel'
        ));
    }

    /**
     * Jadwal Hari Ini
     */
    public function today()
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();
        $hari = ucfirst(now()->locale('id')->dayName);

        $jadwal = JadwalMengajar::with(['kelas', 'mataPelajaran'])
                                ->where('guru_id', $guru->id)
                                ->where('hari', $hari)
                                ->where('status', 'aktif')
                                ->orderBy('jam_mulai')
                                ->get();

        return view('guru.jadwal.today', compact('jadwal', 'hari'));
    }

    /**
     * Detail Jadwal
     */
    public function show($id)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $jadwal = JadwalMengajar::with(['kelas', 'mataPelajaran'])
                                ->where('guru_id', $guru->id)
                                ->findOrFail($id);

        // Get absensi history for this jadwal (last 30 days)
        $riwayat = $jadwal->absensi()
                         ->with('guru')
                         ->whereDate('tanggal', '>=', now()->subDays(30))
                         ->orderBy('tanggal', 'desc')
                         ->paginate(20);

        return view('guru.jadwal.show', compact('jadwal', 'riwayat'));
    }
}
