<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;

class KontakGuruController extends Controller
{
    /**
     * Daftar Kontak Guru
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'aktif');

        $guru = Guru::when($search, function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
                })
                ->when($status, function($q) use ($status) {
                    $q->where('status', $status);
                })
                ->orderBy('nama')
                ->paginate(20)
                ->withQueryString();

        return view('guru-piket.kontak-guru.index', compact('guru', 'search', 'status'));
    }

    /**
     * Detail kontak guru
     */
    public function show($id)
    {
        $guru = Guru::with(['user', 'jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                    ->findOrFail($id);

        // Jadwal mengajar
        $jadwal = $guru->jadwalMengajar()->where('status', 'aktif')
                                          ->orderBy('hari')
                                          ->orderBy('jam_mulai')
                                          ->get()
                                          ->groupBy('hari');

        // Statistik bulan ini
        $stats = [
            'hadir' => $guru->absensi()->whereMonth('tanggal', now()->month)->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $guru->absensi()->whereMonth('tanggal', now()->month)->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $guru->absensi()->whereMonth('tanggal', now()->month)->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => $guru->absensi()->whereMonth('tanggal', now()->month)->where('status_kehadiran', 'alpha')->count(),
        ];

        return view('guru-piket.kontak-guru.show', compact('guru', 'jadwal', 'stats'));
    }

    /**
     * Export kontak guru (untuk print atau simpan)
     */
    public function export()
    {
        $guru = Guru::whereHas('user', function($q) {
                        $q->where('status', 'aktif');
                    })
                    ->orderBy('nama')
                    ->get();

        return view('guru-piket.kontak-guru.export', compact('guru'));
    }
}
