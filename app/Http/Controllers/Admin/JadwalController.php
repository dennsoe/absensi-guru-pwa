<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru, Kelas, MataPelajaran};

class JadwalController extends Controller
{
    /**
     * Display a listing of jadwal mengajar
     */
    public function index(Request $request)
    {
        $query = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])->latest();

        // Filter by guru
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Filter by tahun ajaran
        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jadwal_list = $query->paginate(20)->withQueryString();
        $guru_list = Guru::all();
        $kelas_list = Kelas::all();

        return view('admin.jadwal.index', compact('jadwal_list', 'guru_list', 'kelas_list'));
    }

    /**
     * Show the form for creating a new jadwal
     */
    public function create()
    {
        $guru_list = Guru::all();
        $kelas_list = Kelas::all();
        $mapel_list = MataPelajaran::all();

        return view('admin.jadwal.create', compact('guru_list', 'kelas_list', 'mapel_list'));
    }

    /**
     * Store a newly created jadwal in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan' => 'nullable|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check for conflicts
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
            return back()->withInput()->with('error', 'Jadwal bentrok! Guru sudah memiliki jadwal di waktu tersebut.');
        }

        JadwalMengajar::create($validated);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil ditambahkan.');
    }

    /**
     * Show the form for editing jadwal
     */
    public function edit(JadwalMengajar $jadwal)
    {
        $guru_list = Guru::all();
        $kelas_list = Kelas::all();
        $mapel_list = MataPelajaran::all();

        return view('admin.jadwal.edit', compact('jadwal', 'guru_list', 'kelas_list', 'mapel_list'));
    }

    /**
     * Update jadwal in database
     */
    public function update(Request $request, JadwalMengajar $jadwal)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan' => 'nullable|string|max:50',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Check for conflicts (exclude current jadwal)
        $conflict = JadwalMengajar::where('id', '!=', $jadwal->id)
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
            return back()->withInput()->with('error', 'Jadwal bentrok! Guru sudah memiliki jadwal di waktu tersebut.');
        }

        $jadwal->update($validated);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil diupdate.');
    }

    /**
     * Remove jadwal from database
     */
    public function destroy(JadwalMengajar $jadwal)
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal mengajar berhasil dihapus.');
    }
}
