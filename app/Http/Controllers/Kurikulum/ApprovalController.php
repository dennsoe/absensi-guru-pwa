<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, GuruPengganti};
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Daftar Perubahan Jadwal Pending
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');

        // Jadwal changes (assumed need approval for status updates)
        $jadwal_pending = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran'])
                                       ->where('tahun_ajaran', $tahun_ajaran)
                                       ->where('status', 'pending')
                                       ->orderBy('updated_at', 'desc')
                                       ->get();

        // Guru Pengganti pending
        $pengganti_pending = GuruPengganti::with(['jadwalAsli.guru', 'jadwalAsli.kelas', 'guruPengganti'])
                                         ->when($status, fn($q) => $q->where('status', $status))
                                         ->orderBy('tanggal', 'desc')
                                         ->paginate(20)
                                         ->withQueryString();

        return view('kurikulum.approval.index', compact(
            'jadwal_pending',
            'pengganti_pending',
            'status',
            'tahun_ajaran'
        ));
    }

    /**
     * Detail Approval
     */
    public function show($id)
    {
        $pengganti = GuruPengganti::with([
            'jadwalAsli.guru',
            'jadwalAsli.kelas',
            'jadwalAsli.mataPelajaran',
            'guruPengganti'
        ])->findOrFail($id);

        // Get guru availability on that day
        $jadwal_pengganti = JadwalMengajar::where('guru_id', $pengganti->guru_pengganti_id)
                                          ->where('hari', $pengganti->jadwalAsli->hari)
                                          ->with(['kelas', 'mataPelajaran'])
                                          ->get();

        return view('kurikulum.approval.show', compact('pengganti', 'jadwal_pengganti'));
    }

    /**
     * Approve Guru Pengganti
     */
    public function approve($id)
    {
        $pengganti = GuruPengganti::findOrFail($id);

        if ($pengganti->status !== 'pending') {
            return back()->with('error', 'Penugasan ini sudah diproses sebelumnya.');
        }

        $pengganti->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Penugasan guru pengganti disetujui.');
    }

    /**
     * Reject Guru Pengganti
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        $pengganti = GuruPengganti::findOrFail($id);

        if ($pengganti->status !== 'pending') {
            return back()->with('error', 'Penugasan ini sudah diproses sebelumnya.');
        }

        $pengganti->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'keterangan' => $request->alasan_penolakan,
        ]);

        return back()->with('success', 'Penugasan guru pengganti ditolak.');
    }

    /**
     * Approve Jadwal Status Change
     */
    public function approveJadwal($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        $jadwal->update(['status' => 'aktif']);

        return back()->with('success', 'Jadwal disetujui dan diaktifkan.');
    }

    /**
     * Reject Jadwal Status Change
     */
    public function rejectJadwal($id)
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        $jadwal->update(['status' => 'nonaktif']);

        return back()->with('success', 'Jadwal ditolak.');
    }
}
