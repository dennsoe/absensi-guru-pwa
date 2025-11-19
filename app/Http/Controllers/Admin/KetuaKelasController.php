<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Kelas, Guru, User};
use Illuminate\Support\Facades\{Auth, DB, Log, Hash};

class KetuaKelasController extends Controller
{
    /**
     * Display listing of kelas with ketua kelas
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = Kelas::with(['ketuaKelas', 'waliKelas']);

        // Filter by tingkat
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter by status (with/without ketua)
        if ($request->filled('status')) {
            if ($request->status === 'with_ketua') {
                $query->whereNotNull('ketua_kelas_id');
            } elseif ($request->status === 'without_ketua') {
                $query->whereNull('ketua_kelas_id');
            }
        }

        $kelasList = $query->orderBy('tingkat')->orderBy('nama')->paginate(20);

        // Statistics
        $totalKelas = Kelas::count();
        $kelasWithKetua = Kelas::whereNotNull('ketua_kelas_id')->count();
        $kelasWithoutKetua = $totalKelas - $kelasWithKetua;
        $ketuaKelasAktif = Guru::whereHas('kelasAsKetua')
            
            ->count();

        return view('admin.ketua-kelas.index', compact(
            'kelasList',
            'totalKelas',
            'kelasWithKetua',
            'kelasWithoutKetua',
            'ketuaKelasAktif'
        ));
    }

    /**
     * Show form for assigning ketua kelas
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Get specific kelas if kelas_id is provided
        $kelas = null;
        if ($request->filled('kelas_id')) {
            $kelas = Kelas::with(['ketuaKelas', 'waliKelas'])->find($request->kelas_id);
        }

        // Get all kelas for selection
        $availableKelas = Kelas::with('ketuaKelas')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        // Get all active guru (with kelas count)
        $availableGuru = Guru::withCount('kelasAsKetua')
            
            ->orderBy('nama')
            ->get();

        // Statistics
        $totalKelas = Kelas::count();
        $kelasWithKetua = Kelas::whereNotNull('ketua_kelas_id')->count();
        $kelasWithoutKetua = $totalKelas - $kelasWithKetua;

        return view('admin.ketua-kelas.assign', compact(
            'kelas',
            'availableKelas',
            'availableGuru',
            'totalKelas',
            'kelasWithKetua',
            'kelasWithoutKetua'
        ));
    }

    /**
     * Store ketua kelas assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            DB::beginTransaction();

            $kelas = Kelas::findOrFail($request->kelas_id);
            $guru = Guru::with('user')->findOrFail($request->guru_id);

            // Update kelas with new ketua
            $oldKetuaId = $kelas->ketua_kelas_id;
            $kelas->update([
                'ketua_kelas_id' => $guru->id,
            ]);

            // Update guru user role to include ketua_kelas if not already
            if ($guru->user && $guru->user->role === 'guru') {
                $guru->user->update(['role' => 'ketua_kelas']);
            }

            // If there was a previous ketua, check if they're still ketua of other classes
            if ($oldKetuaId) {
                $oldKetua = Guru::find($oldKetuaId);
                if ($oldKetua && $oldKetua->user) {
                    $stillKetuaCount = Kelas::where('ketua_kelas_id', $oldKetuaId)->count();
                    if ($stillKetuaCount === 0) {
                        // Not ketua of any class anymore, revert to guru role
                        $oldKetua->user->update(['role' => 'guru']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.ketua-kelas.index')
                ->with('success', 'Berhasil menetapkan ' . $guru->nama . ' sebagai ketua kelas ' . $kelas->nama);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing ketua kelas: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove ketua kelas from a class
     */
    public function destroy($kelasId)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            DB::beginTransaction();

            $kelas = Kelas::with('ketuaKelas.user')->findOrFail($kelasId);

            if (!$kelas->ketua_kelas_id) {
                return back()->with('error', 'Kelas ini tidak memiliki ketua kelas.');
            }

            $ketuaNama = $kelas->ketuaKelas->nama;
            $ketuaId = $kelas->ketua_kelas_id;

            // Remove ketua from kelas
            $kelas->update(['ketua_kelas_id' => null]);

            // Check if this guru is still ketua of other classes
            $stillKetuaCount = Kelas::where('ketua_kelas_id', $ketuaId)->count();
            if ($stillKetuaCount === 0) {
                // Not ketua of any class anymore, revert to guru role
                $ketua = Guru::with('user')->find($ketuaId);
                if ($ketua && $ketua->user && $ketua->user->role === 'ketua_kelas') {
                    $ketua->user->update(['role' => 'guru']);
                }
            }

            DB::commit();

            return redirect()->route('admin.ketua-kelas.index')
                ->with('success', 'Berhasil menghapus ' . $ketuaNama . ' sebagai ketua kelas ' . $kelas->nama);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing ketua kelas: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
