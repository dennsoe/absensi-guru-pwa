<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{GuruPiket, Guru};
use Illuminate\Support\Facades\{Auth, DB, Log};

class GuruPiketController extends Controller
{
    /**
     * Display listing of guru piket
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = GuruPiket::with('guru');

        // Filter by hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by guru name
        if ($request->filled('search')) {
            $query->whereHas('guru', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        $guruPiket = $query->orderBy('hari')->orderBy('created_at', 'desc')->get();

        return view('admin.guru-piket.index', compact('guruPiket'));
    }

    /**
     * Show form for assigning guru piket
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Get all active guru
        $availableGuru = Guru::orderBy('nama')->get();

        // Summary by day
        $summaryByDay = GuruPiket::select('hari', DB::raw('count(*) as total'))
            ->groupBy('hari')
            ->get()
            ->pluck('total', 'hari')
            ->toArray();

        $totalGuruPiket = GuruPiket::where('status', 'aktif')->distinct('guru_id')->count();

        return view('admin.guru-piket.assign', compact('availableGuru', 'summaryByDay', 'totalGuruPiket'));
    }

    /**
     * Store guru piket assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            // Check if guru already assigned on this day
            $exists = GuruPiket::where('guru_id', $request->guru_id)
                ->where('hari', $request->hari)
                ->exists();

            if ($exists) {
                return back()
                    ->withInput()
                    ->with('error', 'Guru sudah ditugaskan sebagai piket pada hari ' . $request->hari);
            }

            // Create guru piket assignment
            GuruPiket::create([
                'guru_id' => $request->guru_id,
                'hari' => $request->hari,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            $guru = Guru::find($request->guru_id);

            return redirect()->route('admin.guru-piket.index')
                ->with('success', 'Berhasil menugaskan ' . $guru->nama . ' sebagai guru piket pada hari ' . $request->hari);

        } catch (\Exception $e) {
            Log::error('Error storing guru piket: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove guru piket assignment
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $guruPiket = GuruPiket::with('guru')->findOrFail($id);
            $guruNama = $guruPiket->guru->nama;
            $hari = $guruPiket->hari;

            $guruPiket->delete();

            return redirect()->route('admin.guru-piket.index')
                ->with('success', 'Berhasil menghapus assignment guru piket ' . $guruNama . ' pada hari ' . $hari);

        } catch (\Exception $e) {
            Log::error('Error deleting guru piket: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Update guru piket status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
            }

            $guruPiket = GuruPiket::findOrFail($id);
            $guruPiket->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating guru piket status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }
}
