<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MataPelajaran;

class MataPelajaranController extends Controller
{
    /**
     * Display a listing of mata pelajaran
     */
    public function index(Request $request)
    {
        $query = MataPelajaran::withCount('jadwalMengajar')->latest();

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_mapel', 'like', "%{$search}%")
                  ->orWhere('kode_mapel', 'like', "%{$search}%");
            });
        }

        $mapel_list = $query->paginate(20)->withQueryString();

        return view('admin.mata-pelajaran.index', compact('mapel_list'));
    }

    /**
     * Show the form for creating a new mata pelajaran
     */
    public function create()
    {
        return view('admin.mata-pelajaran.create');
    }

    /**
     * Store a newly created mata pelajaran in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing mata pelajaran
     */
    public function edit(MataPelajaran $mapel)
    {
        return view('admin.mata-pelajaran.edit', compact('mapel'));
    }

    /**
     * Update mata pelajaran in database
     */
    public function update(Request $request, MataPelajaran $mapel)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel,' . $mapel->id,
            'nama_mapel' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        $mapel->update($validated);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diupdate.');
    }

    /**
     * Remove mata pelajaran from database
     */
    public function destroy(MataPelajaran $mapel)
    {
        // Check if mapel is used in jadwal
        if ($mapel->jadwalMengajar()->count() > 0) {
            return redirect()->route('admin.mapel.index')
                ->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan dalam jadwal mengajar.');
        }

        $mapel->delete();

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
