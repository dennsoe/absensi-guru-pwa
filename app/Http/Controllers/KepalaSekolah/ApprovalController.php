<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{IzinCuti, Absensi};
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Daftar Permohonan Izin/Cuti
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $izin = IzinCuti::when($status !== 'all', function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->with('guru')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20)
                    ->withQueryString();

        $stats = [
            'pending' => IzinCuti::where('status', 'pending')->count(),
            'approved' => IzinCuti::where('status', 'approved')->count(),
            'rejected' => IzinCuti::where('status', 'rejected')->count(),
        ];

        return view('kepala-sekolah.approval.index', compact('izin', 'stats', 'status', 'bulan', 'tahun'));
    }

    /**
     * Detail Permohonan
     */
    public function show($id)
    {
        $izin = IzinCuti::with(['guru', 'approvedBy'])
                       ->findOrFail($id);

        // Riwayat absensi guru bulan ini
        $riwayat_absensi = Absensi::where('guru_id', $izin->guru_id)
                                  ->whereMonth('tanggal', now()->month)
                                  ->with('jadwal')
                                  ->orderBy('tanggal', 'desc')
                                  ->take(10)
                                  ->get();

        return view('kepala-sekolah.approval.show', compact('izin', 'riwayat_absensi'));
    }

    /**
     * Approve Izin
     */
    public function approve(Request $request, $id)
    {
        $izin = IzinCuti::findOrFail($id);
        
        if ($izin->status !== 'pending') {
            return back()->with('error', 'Permohonan sudah diproses sebelumnya.');
        }

        $izin->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_approval' => $request->catatan_approval,
        ]);

        return redirect()->route('kepala-sekolah.approval.index')
                        ->with('success', 'Permohonan izin telah disetujui.');
    }

    /**
     * Reject Izin
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        $izin = IzinCuti::findOrFail($id);
        
        if ($izin->status !== 'pending') {
            return back()->with('error', 'Permohonan sudah diproses sebelumnya.');
        }

        $izin->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_approval' => $request->alasan_penolakan,
        ]);

        return redirect()->route('kepala-sekolah.approval.index')
                        ->with('success', 'Permohonan izin telah ditolak.');
    }

    /**
     * Approve Multiple
     */
    public function approveMultiple(Request $request)
    {
        $request->validate([
            'izin_ids' => 'required|array',
            'izin_ids.*' => 'exists:izin_cuti,id',
        ]);

        IzinCuti::whereIn('id', $request->izin_ids)
               ->where('status', 'pending')
               ->update([
                   'status' => 'approved',
                   'approved_by' => Auth::id(),
                   'approved_at' => now(),
               ]);

        return back()->with('success', count($request->izin_ids) . ' permohonan berhasil disetujui.');
    }
}
