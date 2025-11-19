<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinCuti;
use App\Models\Guru;
use App\Services\IzinCutiService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IzinController extends Controller
{
    protected $izinCutiService;

    public function __construct(IzinCutiService $izinCutiService)
    {
        $this->izinCutiService = $izinCutiService;
    }
    /**
     * Display a listing of izin/cuti.
     */
    public function index(Request $request)
    {
        $query = IzinCuti::with(['guru.user', 'disetujuiOleh', 'guruPengganti.user']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by jenis
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }

        // Filter by date range
        if ($request->has('tanggal_mulai') && $request->tanggal_mulai != '') {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && $request->tanggal_selesai != '') {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('guru.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $izins = $query->orderBy('tanggal_mulai', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $totalIzin = IzinCuti::count();
        $pending = IzinCuti::where('status', 'pending')->count();
        $approved = IzinCuti::where('status', 'approved')->count();
        $rejected = IzinCuti::where('status', 'rejected')->count();

        return view('admin.izin.index', compact(
            'izins',
            'totalIzin',
            'pending',
            'approved',
            'rejected'
        ));
    }

    /**
     * Display the specified izin.
     */
    public function show($id)
    {
        $izin = IzinCuti::with(['guru.user', 'disetujuiOleh', 'guruPengganti.user'])->findOrFail($id);

        return view('admin.izin.show', compact('izin'));
    }

    /**
     * Approve izin.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $result = $this->izinCutiService->approveIzinCuti(
            $id,
            auth()->id(),
            $request->input('catatan')
        );

        if ($result['success']) {
            return redirect()->route('admin.izin.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.izin.index')
            ->with('error', $result['message']);
    }

    /**
     * Reject izin.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        $result = $this->izinCutiService->rejectIzinCuti(
            $id,
            auth()->id(),
            $request->alasan_penolakan
        );

        if ($result['success']) {
            return redirect()->route('admin.izin.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.izin.index')
            ->with('error', $result['message']);
    }

    /**
     * Delete izin (soft delete).
     */
    public function destroy($id)
    {
        $izin = IzinCuti::findOrFail($id);

        // Only allow deletion if status is pending
        if ($izin->status !== 'pending') {
            return redirect()->route('admin.izin.index')
                ->with('error', 'Hanya izin dengan status pending yang dapat dihapus.');
        }

        $izin->delete();

        return redirect()->route('admin.izin.index')
            ->with('success', 'Izin berhasil dihapus.');
    }
}
