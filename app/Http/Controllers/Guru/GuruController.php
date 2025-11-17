<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Guru, Absensi, JadwalMengajar, IzinCuti};
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    /**
     * Dashboard Guru
     */
    public function dashboard()
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $data = [
            'guru' => $guru,
            'jadwal_hari_ini' => JadwalMengajar::where('guru_id', $guru->id)
                                               ->where('hari', now()->locale('id')->dayName)
                                               ->where('status', 'aktif')
                                               ->with(['kelas', 'mataPelajaran'])
                                               ->orderBy('jam_mulai')
                                               ->get(),
            'absensi_bulan_ini' => Absensi::where('guru_id', $guru->id)
                                          ->whereMonth('tanggal', now()->month)
                                          ->whereYear('tanggal', now()->year)
                                          ->get(),
            'total_hadir' => Absensi::where('guru_id', $guru->id)
                                    ->whereMonth('tanggal', now()->month)
                                    ->whereIn('status', ['hadir', 'terlambat'])
                                    ->count(),
            'total_terlambat' => Absensi::where('guru_id', $guru->id)
                                        ->whereMonth('tanggal', now()->month)
                                        ->where('status', 'terlambat')
                                        ->count(),
            'total_izin' => IzinCuti::where('guru_id', $guru->id)
                                    ->whereMonth('tanggal_mulai', now()->month)
                                    ->where('status', 'disetujui')
                                    ->count(),
        ];

        return view('guru.dashboard', $data);
    }

    /**
     * Riwayat Absensi Guru
     */
    public function riwayatAbsensi(Request $request)
    {
        $guru = Auth::user()->guru;

        $query = Absensi::where('guru_id', $guru->id)
                        ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $absensi = $query->latest('tanggal')->paginate(20);

        return view('guru.absensi.riwayat', compact('absensi'));
    }

    /**
     * Detail Absensi
     */
    public function detailAbsensi(Absensi $absensi)
    {
        // Pastikan absensi milik guru yang login
        if ($absensi->guru_id !== Auth::user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $absensi->load(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran', 'ketuaKelas']);

        return view('guru.absensi.detail', compact('absensi'));
    }
}
