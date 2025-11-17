<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru, Kelas, MataPelajaran};

class JadwalMengajarController extends Controller
{
    /**
     * Daftar Jadwal Mengajar
     */
    public function index(Request $request)
    {
        $guru_id = $request->get('guru_id');
        $kelas_id = $request->get('kelas_id');
        $hari = $request->get('hari');
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');

        $jadwal = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
                                ->when($guru_id, fn($q) => $q->where('guru_id', $guru_id))
                                ->when($kelas_id, fn($q) => $q->where('kelas_id', $kelas_id))
                                ->when($hari, fn($q) => $q->where('hari', $hari))
                                ->where('tahun_ajaran', $tahun_ajaran)
                                ->orderBy('hari')
                                ->orderBy('jam_mulai')
                                ->paginate(20)
                                ->withQueryString();

        $guru_list = Guru::orderBy('nama')->get();
        $kelas_list = Kelas::orderBy('nama_kelas')->get();

        return view('kurikulum.jadwal.index', compact(
            'jadwal',
            'guru_list',
            'kelas_list',
            'guru_id',
            'kelas_id',
            'hari',
            'tahun_ajaran'
        ));
    }

    /**
     * Form Tambah Jadwal
     */
    public function create()
    {
        $guru_list = Guru::whereHas('user', function($q) {
            $q->where('status', 'aktif');
        })->orderBy('nama')->get();
        $kelas_list = Kelas::orderBy('nama_kelas')->get();
        $mapel_list = MataPelajaran::orderBy('nama_mapel')->get();

        return view('kurikulum.jadwal.create', compact('guru_list', 'kelas_list', 'mapel_list'));
    }

    /**
     * Simpan Jadwal Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruangan' => 'nullable|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:1,2',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check conflict
        $conflict = JadwalMengajar::where('guru_id', $validated['guru_id'])
                                  ->where('hari', $validated['hari'])
                                  ->where('tahun_ajaran', $validated['tahun_ajaran'])
                                  ->where('semester', $validated['semester'])
                                  ->where(function($q) use ($validated) {
                                      $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                                        ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                                        ->orWhere(function($q2) use ($validated) {
                                            $q2->where('jam_mulai', '<=', $validated['jam_mulai'])
                                               ->where('jam_selesai', '>=', $validated['jam_selesai']);
                                        });
                                  })
                                  ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Terjadi bentrok jadwal! Guru sudah memiliki jadwal pada hari ' . ucfirst($validated['hari']) . ' jam ' . $validated['jam_mulai'] . ' - ' . $validated['jam_selesai']);
        }

        JadwalMengajar::create($validated);

        return redirect()->route('kurikulum.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Form Edit Jadwal
     */
    public function edit($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        $guru_list = Guru::whereHas('user', function($q) {
            $q->where('status', 'aktif');
        })->orderBy('nama')->get();
        $kelas_list = Kelas::orderBy('nama_kelas')->get();
        $mapel_list = MataPelajaran::orderBy('nama_mapel')->get();

        return view('kurikulum.jadwal.edit', compact('jadwal', 'guru_list', 'kelas_list', 'mapel_list'));
    }

    /**
     * Update Jadwal
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'ruangan' => 'nullable|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:1,2',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check conflict (exclude current)
        $conflict = JadwalMengajar::where('id', '!=', $id)
                                  ->where('guru_id', $validated['guru_id'])
                                  ->where('hari', $validated['hari'])
                                  ->where('tahun_ajaran', $validated['tahun_ajaran'])
                                  ->where('semester', $validated['semester'])
                                  ->where(function($q) use ($validated) {
                                      $q->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                                        ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                                        ->orWhere(function($q2) use ($validated) {
                                            $q2->where('jam_mulai', '<=', $validated['jam_mulai'])
                                               ->where('jam_selesai', '>=', $validated['jam_selesai']);
                                        });
                                  })
                                  ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Terjadi bentrok jadwal!');
        }

        $jadwal->update($validated);

        return redirect()->route('kurikulum.jadwal.index')->with('success', 'Jadwal berhasil diupdate.');
    }

    /**
     * Hapus Jadwal
     */
    public function destroy($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        // Check if has absensi
        if ($jadwal->absensi()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus jadwal yang sudah memiliki data absensi.');
        }

        $jadwal->delete();

        return redirect()->route('kurikulum.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}
