<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru, Kelas, MataPelajaran, GuruPengganti, IzinCuti, Absensi};
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;

class KurikulumController extends Controller
{
    /**
     * Dashboard Kurikulum
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role !== 'kurikulum') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Hanya Tim Kurikulum yang dapat mengakses halaman ini.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
        $tanggal = Carbon::today();

        // STATISTIK UMUM
        $total_guru = Guru::whereHas('user', function($q) {
            $q;
        })->count();

        $total_kelas = Kelas::count();
        $total_mapel = MataPelajaran::count();
        $total_jadwal_aktif = JadwalMengajar::where('status', 'aktif')->count();

        // JADWAL HARI INI
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
            
            ->with(['guru.user', 'kelas', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        $total_jadwal_hari_ini = $jadwal_hari_ini->count();

        // GURU YANG IZIN/CUTI HARI INI (perlu pengganti)
        $guru_izin_hari_ini = IzinCuti::where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->with('guru.user')
            ->get();

        $jadwal_perlu_pengganti = collect();
        foreach ($guru_izin_hari_ini as $izin) {
            $jadwal_guru = JadwalMengajar::where('guru_id', $izin->guru_id)
                ->where('hari', $hari_ini)
                
                ->with(['guru.user', 'kelas', 'mataPelajaran'])
                ->get();

            $jadwal_perlu_pengganti = $jadwal_perlu_pengganti->merge($jadwal_guru);
        }

        // DETEKSI KONFLIK JADWAL
        $konflik_jadwal = $this->detectConflicts();

        return view('kurikulum.dashboard', [
            'statistik' => [
                'total_guru' => $total_guru,
                'total_kelas' => $total_kelas,
                'total_mapel' => $total_mapel,
                'total_jadwal_aktif' => $total_jadwal_aktif,
                'total_jadwal_hari_ini' => $total_jadwal_hari_ini,
                'perlu_pengganti' => $jadwal_perlu_pengganti->count(),
                'konflik_jadwal' => count($konflik_jadwal),
            ],
            'jadwal_hari_ini' => $jadwal_hari_ini,
            'jadwal_perlu_pengganti' => $jadwal_perlu_pengganti,
            'konflik_jadwal' => $konflik_jadwal,
            'guru_izin_hari_ini' => $guru_izin_hari_ini,
            'hari_ini' => $hari_ini,
            'tanggal' => $tanggal->format('d F Y'),
        ]);
    }

    /**
     * Detect Scheduling Conflicts
     */
    private function detectConflicts()
    {
        $conflicts = [];

        // Ambil semua jadwal aktif
        $jadwal_aktif = JadwalMengajar::where('status', 'aktif')
            ->with(['guru.user', 'kelas', 'mataPelajaran'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Group by hari
        $jadwal_per_hari = $jadwal_aktif->groupBy('hari');

        foreach ($jadwal_per_hari as $hari => $jadwals) {
            foreach ($jadwals as $j1) {
                foreach ($jadwals as $j2) {
                    if ($j1->id >= $j2->id) continue;

                    // Cek conflict guru yang sama di waktu yang sama
                    if ($j1->guru_id === $j2->guru_id) {
                        if ($this->isTimeOverlap($j1->jam_mulai, $j1->jam_selesai, $j2->jam_mulai, $j2->jam_selesai)) {
                            $conflicts[] = [
                                'type' => 'guru',
                                'hari' => $hari,
                                'jadwal1' => $j1,
                                'jadwal2' => $j2,
                                'message' => "Guru {$j1->guru->user->name} mengajar di 2 kelas pada waktu yang sama",
                            ];
                        }
                    }

                    // Cek conflict kelas yang sama di waktu yang sama
                    if ($j1->kelas_id === $j2->kelas_id) {
                        if ($this->isTimeOverlap($j1->jam_mulai, $j1->jam_selesai, $j2->jam_mulai, $j2->jam_selesai)) {
                            $conflicts[] = [
                                'type' => 'kelas',
                                'hari' => $hari,
                                'jadwal1' => $j1,
                                'jadwal2' => $j2,
                                'message' => "Kelas {$j1->kelas->nama_kelas} memiliki 2 pelajaran pada waktu yang sama",
                            ];
                        }
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Check if two time ranges overlap
     */
    private function isTimeOverlap($start1, $end1, $start2, $end2)
    {
        $start1 = Carbon::createFromFormat('H:i:s', $start1);
        $end1 = Carbon::createFromFormat('H:i:s', $end1);
        $start2 = Carbon::createFromFormat('H:i:s', $start2);
        $end2 = Carbon::createFromFormat('H:i:s', $end2);

        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Kelola Jadwal Mengajar - List
     */
    public function jadwal(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kurikulum') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = JadwalMengajar::with(['guru.user', 'kelas', 'mataPelajaran']);

        // Filter
        if ($request->has('guru_id') && $request->guru_id) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->has('kelas_id') && $request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->has('hari') && $request->hari) {
            $query->where('hari', $request->hari);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $jadwal = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(50);

        $guru_list = Guru::with('user')->whereHas('user', function($q) {
            $q;
        })->get();

        $kelas_list = Kelas::all();

        return view('kurikulum.jadwal.index', [
            'jadwal' => $jadwal,
            'guru_list' => $guru_list,
            'kelas_list' => $kelas_list,
            'filters' => $request->only(['guru_id', 'kelas_id', 'hari', 'status']),
        ]);
    }

    /**
     * Form Create Jadwal
     */
    public function createJadwal()
    {
        $user = Auth::user();

        if ($user->role !== 'kurikulum') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $guru = Guru::with('user')->whereHas('user', function($q) {
            $q;
        })->get();

        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();

        return view('kurikulum.jadwal.create', [
            'guru' => $guru,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    /**
     * Store Jadwal dengan Conflict Detection
     */
    public function storeJadwal(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'nullable|string|max:20',
            'semester' => 'nullable|in:ganjil,genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'kurikulum') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            // CEK KONFLIK GURU (guru sama, hari sama, waktu overlap)
            $konflik_guru = JadwalMengajar::where('guru_id', $request->guru_id)
                ->where('hari', $request->hari)
                
                ->get()
                ->filter(function($jadwal) use ($request) {
                    return $this->isTimeOverlap(
                        $jadwal->jam_mulai,
                        $jadwal->jam_selesai,
                        $request->jam_mulai . ':00',
                        $request->jam_selesai . ':00'
                    );
                });

            if ($konflik_guru->isNotEmpty()) {
                $j = $konflik_guru->first();
                return back()->withErrors([
                    'jam_mulai' => "KONFLIK: Guru sudah mengajar di kelas {$j->kelas->nama_kelas} pada {$j->jam_mulai} - {$j->jam_selesai}"
                ])->withInput();
            }

            // CEK KONFLIK KELAS (kelas sama, hari sama, waktu overlap)
            $konflik_kelas = JadwalMengajar::where('kelas_id', $request->kelas_id)
                ->where('hari', $request->hari)
                
                ->get()
                ->filter(function($jadwal) use ($request) {
                    return $this->isTimeOverlap(
                        $jadwal->jam_mulai,
                        $jadwal->jam_selesai,
                        $request->jam_mulai . ':00',
                        $request->jam_selesai . ':00'
                    );
                });

            if ($konflik_kelas->isNotEmpty()) {
                $j = $konflik_kelas->first();
                return back()->withErrors([
                    'jam_mulai' => "KONFLIK: Kelas sudah ada pelajaran {$j->mataPelajaran->nama_mapel} pada {$j->jam_mulai} - {$j->jam_selesai}"
                ])->withInput();
            }

            // SIMPAN JADWAL
            JadwalMengajar::create([
                'guru_id' => $request->guru_id,
                'kelas_id' => $request->kelas_id,
                'mapel_id' => $request->mapel_id,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai . ':00',
                'jam_selesai' => $request->jam_selesai . ':00',
                'tahun_ajaran' => $request->tahun_ajaran ?? Carbon::now()->year . '/' . (Carbon::now()->year + 1),
                'semester' => $request->semester ?? 'ganjil',
                'status' => $request->status,
            ]);

            return redirect()->route('kurikulum.jadwal')
                ->with('success', 'Jadwal mengajar berhasil ditambahkan.');

        } catch (\Exception $e) {
            \Log::error('Error store jadwal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form Edit Jadwal
     */
    public function editJadwal($id)
    {
        $user = Auth::user();

        if ($user->role !== 'kurikulum') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $jadwal = JadwalMengajar::with(['guru.user', 'kelas', 'mataPelajaran'])->findOrFail($id);

        $guru = Guru::with('user')->whereHas('user', function($q) {
            $q;
        })->get();

        $kelas = Kelas::all();
        $mapel = MataPelajaran::all();

        return view('kurikulum.jadwal.edit', [
            'jadwal' => $jadwal,
            'guru' => $guru,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    /**
     * Update Jadwal dengan Conflict Detection
     */
    public function updateJadwal(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tahun_ajaran' => 'nullable|string|max:20',
            'semester' => 'nullable|in:ganjil,genap',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'kurikulum') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.',
                ], 403);
            }

            $jadwal = JadwalMengajar::findOrFail($id);

            // CEK KONFLIK GURU (exclude jadwal ini)
            $konflik_guru = JadwalMengajar::where('guru_id', $request->guru_id)
                ->where('hari', $request->hari)
                
                ->where('id', '!=', $id)
                ->get()
                ->filter(function($j) use ($request) {
                    return $this->isTimeOverlap(
                        $j->jam_mulai,
                        $j->jam_selesai,
                        $request->jam_mulai . ':00',
                        $request->jam_selesai . ':00'
                    );
                });

            if ($konflik_guru->isNotEmpty()) {
                $j = $konflik_guru->first();
                return back()->withErrors([
                    'jam_mulai' => "KONFLIK: Guru sudah mengajar di kelas {$j->kelas->nama_kelas} pada {$j->jam_mulai} - {$j->jam_selesai}"
                ])->withInput();
            }

            // CEK KONFLIK KELAS (exclude jadwal ini)
            $konflik_kelas = JadwalMengajar::where('kelas_id', $request->kelas_id)
                ->where('hari', $request->hari)
                
                ->where('id', '!=', $id)
                ->get()
                ->filter(function($j) use ($request) {
                    return $this->isTimeOverlap(
                        $j->jam_mulai,
                        $j->jam_selesai,
                        $request->jam_mulai . ':00',
                        $request->jam_selesai . ':00'
                    );
                });

            if ($konflik_kelas->isNotEmpty()) {
                $j = $konflik_kelas->first();
                return back()->withErrors([
                    'jam_mulai' => "KONFLIK: Kelas sudah ada pelajaran {$j->mataPelajaran->nama_mapel} pada {$j->jam_mulai} - {$j->jam_selesai}"
                ])->withInput();
            }

            // UPDATE JADWAL
            $jadwal->update([
                'guru_id' => $request->guru_id,
                'kelas_id' => $request->kelas_id,
                'mapel_id' => $request->mapel_id,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai . ':00',
                'jam_selesai' => $request->jam_selesai . ':00',
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester,
                'status' => $request->status,
            ]);

            return redirect()->route('kurikulum.jadwal')
                ->with('success', 'Jadwal mengajar berhasil diupdate.');

        } catch (\Exception $e) {
            \Log::error('Error update jadwal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete Jadwal
     */
    public function destroyJadwal($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'kurikulum') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $jadwal = JadwalMengajar::findOrFail($id);

            // Cek apakah ada absensi terkait
            $ada_absensi = Absensi::where('jadwal_id', $id)->exists();

            if ($ada_absensi) {
                return redirect()->route('kurikulum.jadwal')
                    ->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki data absensi. Ubah status menjadi non-aktif sebagai gantinya.');
            }

            $jadwal->delete();

            return redirect()->route('kurikulum.jadwal')
                ->with('success', 'Jadwal mengajar berhasil dihapus.');

        } catch (\Exception $e) {
            \Log::error('Error delete jadwal: ' . $e->getMessage());
            return redirect()->route('kurikulum.jadwal')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Akademik per Guru
     */
    public function laporanAkademik(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kurikulum') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        $laporan = Guru::with('user')
            ->whereHas('user', function($q) {
                $q;
            })
            ->get()
            ->map(function($guru) use ($bulan, $tahun) {
                $total_jadwal = JadwalMengajar::where('guru_id', $guru->id)
                    
                    ->count();

                $total_mengajar = Absensi::whereHas('jadwal', function($q) use ($guru) {
                        $q->where('guru_id', $guru->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->count();

                $guru->laporan = [
                    'total_jadwal' => $total_jadwal,
                    'total_mengajar' => $total_mengajar,
                    'persentase' => $total_jadwal > 0 ? round(($total_mengajar / ($total_jadwal * 4)) * 100, 1) : 0, // estimasi 4 minggu
                ];

                return $guru;
            })
            ->sortByDesc(function($guru) {
                return $guru->laporan['total_mengajar'];
            })
            ->values();

        return view('kurikulum.laporan.akademik', [
            'laporan' => $laporan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_nama' => Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName,
        ]);
    }
}
