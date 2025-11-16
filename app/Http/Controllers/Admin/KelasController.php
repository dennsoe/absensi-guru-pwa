<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Kelas, Guru, User};

class KelasController extends Controller
{
    /**
     * Display a listing of kelas
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['waliKelas', 'ketuaKelas'])->latest();

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%");
            });
        }

        // Filter tingkat
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter tahun ajaran
        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        $kelas_list = $query->paginate(20)->withQueryString();

        return view('admin.kelas.index', compact('kelas_list'));
    }

    /**
     * Show the form for creating a new kelas
     */
    public function create()
    {
        $guru_list = Guru::all();
        $ketua_kelas_list = User::where('role', 'ketua_kelas')
                                ->whereNull('kelas_id')
                                ->get();

        return view('admin.kelas.create', compact('guru_list', 'ketua_kelas_list'));
    }

    /**
     * Store a newly created kelas in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:50|unique:kelas,nama_kelas',
            'tingkat' => 'required|integer|min:10|max:12',
            'jurusan' => 'nullable|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru,id',
            'ketua_kelas_user_id' => 'nullable|exists:users,id',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        $kelas = Kelas::create($validated);

        // Update kelas_id pada user ketua_kelas jika dipilih
        if ($validated['ketua_kelas_user_id']) {
            User::where('id', $validated['ketua_kelas_user_id'])
                ->update(['kelas_id' => $kelas->id]);
        }

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Show the form for editing kelas
     */
    public function edit(Kelas $kela)
    {
        $guru_list = Guru::all();
        $ketua_kelas_list = User::where('role', 'ketua_kelas')
                                ->where(function($q) use ($kela) {
                                    $q->whereNull('kelas_id')
                                      ->orWhere('kelas_id', $kela->id);
                                })
                                ->get();

        return view('admin.kelas.edit', compact('kela', 'guru_list', 'ketua_kelas_list'));
    }

    /**
     * Update kelas in database
     */
    public function update(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:50|unique:kelas,nama_kelas,' . $kela->id,
            'tingkat' => 'required|integer|min:10|max:12',
            'jurusan' => 'nullable|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru,id',
            'ketua_kelas_user_id' => 'nullable|exists:users,id',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        // Reset kelas_id dari ketua kelas lama jika berubah
        if ($kela->ketua_kelas_user_id && $kela->ketua_kelas_user_id != $validated['ketua_kelas_user_id']) {
            User::where('id', $kela->ketua_kelas_user_id)->update(['kelas_id' => null]);
        }

        $kela->update($validated);

        // Update kelas_id pada user ketua_kelas baru
        if ($validated['ketua_kelas_user_id']) {
            User::where('id', $validated['ketua_kelas_user_id'])
                ->update(['kelas_id' => $kela->id]);
        }

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    /**
     * Remove kelas from database
     */
    public function destroy(Kelas $kela)
    {
        // Reset kelas_id dari ketua kelas jika ada
        if ($kela->ketua_kelas_user_id) {
            User::where('id', $kela->ketua_kelas_user_id)->update(['kelas_id' => null]);
        }

        $kela->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
