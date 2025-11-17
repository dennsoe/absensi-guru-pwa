<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{GuruPengganti, JadwalMengajar, Guru, IzinCuti};
use Illuminate\Support\Facades\Auth;

class GuruPenggantiController extends Controller
{
    /**
     * Daftar Guru Pengganti
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', today()->toDateString());
        $status = $request->get('status');

        $pengganti = GuruPengganti::with(['jadwalAsli.guru', 'jadwalAsli.kelas', 'jadwalAsli.mataPelajaran', 'guruPengganti'])
                                  ->when($tanggal, fn($q) => $q->whereDate('tanggal', $tanggal))
                                  ->when($status, fn($q) => $q->where('status', $status))
                                  ->orderBy('tanggal', 'desc')
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(20)
                                  ->withQueryString();

        return view('kurikulum.guru-pengganti.index', compact('pengganti', 'tanggal', 'status'));
    }

    /**
     * Form Assign Guru Pengganti
     */
    public function create()
    {
        // Guru yang sedang izin hari ini
        $guru_izin = IzinCuti::where('status', 'approved')
                             ->whereDate('tanggal_mulai', '<=', today())
                             ->whereDate('tanggal_selesai', '>=', today())
                             ->with('guru')
                             ->get();

        // Jadwal hari ini
        $hari_ini = ucfirst(now()->locale('id')->dayName);
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                        ->where('status', 'aktif')
                                        ->with(['guru', 'kelas', 'mataPelajaran'])
                                        ->orderBy('jam_mulai')
                                        ->get();

        // Guru yang available (tidak izin, tidak punya jadwal bentrok)
        $guru_available = Guru::whereHas('user', function($q) {
                                    $q->where('status', 'aktif');
                                })
                             ->whereDoesntHave('izinCuti', function($q) {
                                 $q->where('status', 'approved')
                                   ->whereDate('tanggal_mulai', '<=', today())
                                   ->whereDate('tanggal_selesai', '>=', today());
                             })
                             ->orderBy('nama')
                             ->get();

        return view('kurikulum.guru-pengganti.create', compact(
            'guru_izin',
            'jadwal_hari_ini',
            'guru_available'
        ));
    }

    /**
     * Simpan Guru Pengganti
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
            'guru_pengganti_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $validated['status'] = 'pending';
        $validated['dibuat_oleh'] = Auth::id();

        GuruPengganti::create($validated);

        return redirect()->route('kurikulum.guru-pengganti.index')
                        ->with('success', 'Guru pengganti berhasil ditugaskan.');
    }

    /**
     * Approve Assignment
     */
    public function approve($id)
    {
        $pengganti = GuruPengganti::findOrFail($id);

        $pengganti->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Penugasan guru pengganti disetujui.');
    }

    /**
     * Reject Assignment
     */
    public function reject(Request $request, $id)
    {
        $pengganti = GuruPengganti::findOrFail($id);

        $pengganti->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'keterangan' => $request->alasan_penolakan,
        ]);

        return back()->with('success', 'Penugasan guru pengganti ditolak.');
    }

    /**
     * Hapus
     */
    public function destroy($id)
    {
        $pengganti = GuruPengganti::findOrFail($id);

        if ($pengganti->status === 'approved') {
            return back()->with('error', 'Tidak dapat menghapus penugasan yang sudah disetujui.');
        }

        $pengganti->delete();

        return back()->with('success', 'Penugasan guru pengganti dihapus.');
    }
}
