<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru, Kelas, MataPelajaran, GuruPengganti, IzinCuti};
use Illuminate\Support\Facades\DB;

class KurikulumController extends Controller
{
    /**
     * Dashboard Kurikulum
     */
    public function dashboard()
    {
        $hari_ini = ucfirst(now()->locale('id')->dayName);

        // Total jadwal hari ini
        $total_jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                                ->where('status', 'aktif')
                                                ->count();

        // Jadwal yang perlu pengganti (guru izin/cuti)
        $perlu_pengganti = JadwalMengajar::where('hari', $hari_ini)
            ->where('status', 'aktif')
            ->whereHas('guru.izinCuti', function($q) {
                $q->whereDate('tanggal_mulai', '<=', today())
                  ->whereDate('tanggal_selesai', '>=', today())
                  ->where('status', 'approved');
            })
            ->count();

        // Konflik jadwal (guru/kelas double booking)
        $konflik_jadwal = 0; // Placeholder

        // Total guru
        $total_guru = Guru::count();

        // Jadwal hari ini lengkap
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                          ->where('status', 'aktif')
                                          ->with(['guru', 'kelas', 'mataPelajaran', 'guruPengganti'])
                                          ->orderBy('jam_mulai')
                                          ->get();

        // Jadwal perlu pengganti detail
        $jadwal_perlu_pengganti = JadwalMengajar::where('hari', $hari_ini)
            ->where('status', 'aktif')
            ->whereHas('guru.izinCuti', function($q) {
                $q->whereDate('tanggal_mulai', '<=', today())
                  ->whereDate('tanggal_selesai', '>=', today())
                  ->where('status', 'approved');
            })
            ->with(['guru', 'kelas', 'mataPelajaran', 'guruPengganti'])
            ->get();

        // Statistik minggu ini
        $stat_minggu_ini = [
            'total_jadwal' => JadwalMengajar::where('status', 'aktif')->count(),
            'total_pengganti' => GuruPengganti::whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_konflik' => 0,
            'jadwal_berjalan' => JadwalMengajar::where('status', 'aktif')->count(),
        ];

        return view('kurikulum.dashboard', compact(
            'total_jadwal_hari_ini',
            'perlu_pengganti',
            'konflik_jadwal',
            'total_guru',
            'jadwal_hari_ini',
            'jadwal_perlu_pengganti',
            'stat_minggu_ini'
        ));
    }

    /**
     * Kelola Jadwal Mengajar
     */
    public function jadwal()
    {
        $jadwal = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
                                ->latest()
                                ->paginate(50);
        return view('kurikulum.jadwal.index', compact('jadwal'));
    }

    public function createJadwal()
    {
        $guru = Guru::whereHas('user', function($q) {
            $q->where('status', 'aktif');
        })->get();
        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();

        return view('kurikulum.jadwal.create', compact('guru', 'kelas', 'mapel'));
    }

    public function storeJadwal(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Cek konflik jadwal
        $konflik = JadwalMengajar::where('guru_id', $validated['guru_id'])
                                 ->where('hari', $validated['hari'])
                                 ->where('status', 'aktif')
                                 ->where(function($q) use ($validated) {
                                     $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                                       ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']]);
                                 })
                                 ->exists();

        if ($konflik) {
            return back()->withErrors(['hari' => 'Guru sudah memiliki jadwal di waktu yang sama.'])
                        ->withInput();
        }

        JadwalMengajar::create($validated);

        return redirect()->route('kurikulum.jadwal')
                        ->with('success', 'Jadwal mengajar berhasil ditambahkan.');
    }

    public function editJadwal(JadwalMengajar $jadwal)
    {
        $guru = Guru::whereHas('user', function($q) {
            $q->where('status', 'aktif');
        })->get();
        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();

        return view('kurikulum.jadwal.edit', compact('jadwal', 'guru', 'kelas', 'mapel'));
    }

    public function updateJadwal(Request $request, JadwalMengajar $jadwal)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $jadwal->update($validated);

        return redirect()->route('kurikulum.jadwal')
                        ->with('success', 'Jadwal mengajar berhasil diupdate.');
    }

    public function destroyJadwal(JadwalMengajar $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('kurikulum.jadwal')
                        ->with('success', 'Jadwal mengajar berhasil dihapus.');
    }
}
