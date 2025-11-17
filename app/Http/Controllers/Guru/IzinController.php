<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{IzinCuti, Guru};
use Illuminate\Support\Facades\{Auth, Storage};

class IzinController extends Controller
{
    /**
     * Daftar Izin/Cuti
     */
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $status = $request->get('status');
        $jenis = $request->get('jenis');

        $izin = IzinCuti::where('guru_id', $guru->id)
                        ->when($status, fn($q) => $q->where('status', $status))
                        ->when($jenis, fn($q) => $q->where('jenis', $jenis))
                        ->orderBy('tanggal_mulai', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        // Statistics
        $total_pending = IzinCuti::where('guru_id', $guru->id)->where('status', 'pending')->count();
        $total_approved = IzinCuti::where('guru_id', $guru->id)->where('status', 'approved')->count();
        $total_rejected = IzinCuti::where('guru_id', $guru->id)->where('status', 'rejected')->count();

        return view('guru.izin.index', compact(
            'izin',
            'status',
            'jenis',
            'total_pending',
            'total_approved',
            'total_rejected'
        ));
    }

    /**
     * Form Ajukan Izin/Cuti
     */
    public function create()
    {
        return view('guru.izin.create');
    }

    /**
     * Simpan Izin/Cuti
     */
    public function store(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'jenis' => 'required|in:izin,cuti,sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'file_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['guru_id'] = $guru->id;
        $validated['status'] = 'pending';

        if ($request->hasFile('file_pendukung')) {
            $file = $request->file('file_pendukung');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('izin-cuti', $filename, 'public');
            $validated['file_pendukung'] = $path;
        }

        IzinCuti::create($validated);

        return redirect()->route('guru.izin.index')
                        ->with('success', 'Permohonan izin/cuti berhasil diajukan.');
    }

    /**
     * Detail Izin/Cuti
     */
    public function show($id)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $izin = IzinCuti::where('guru_id', $guru->id)->findOrFail($id);

        return view('guru.izin.show', compact('izin'));
    }

    /**
     * Form Edit Izin/Cuti (only pending)
     */
    public function edit($id)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $izin = IzinCuti::where('guru_id', $guru->id)
                       ->where('status', 'pending')
                       ->findOrFail($id);

        return view('guru.izin.edit', compact('izin'));
    }

    /**
     * Update Izin/Cuti
     */
    public function update(Request $request, $id)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $izin = IzinCuti::where('guru_id', $guru->id)
                       ->where('status', 'pending')
                       ->findOrFail($id);

        $validated = $request->validate([
            'jenis' => 'required|in:izin,cuti,sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'file_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_pendukung')) {
            // Delete old file
            if ($izin->file_pendukung) {
                Storage::disk('public')->delete($izin->file_pendukung);
            }

            $file = $request->file('file_pendukung');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('izin-cuti', $filename, 'public');
            $validated['file_pendukung'] = $path;
        }

        $izin->update($validated);

        return redirect()->route('guru.izin.index')
                        ->with('success', 'Permohonan izin/cuti berhasil diupdate.');
    }

    /**
     * Batalkan Izin/Cuti (only pending)
     */
    public function destroy($id)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        $izin = IzinCuti::where('guru_id', $guru->id)
                       ->where('status', 'pending')
                       ->findOrFail($id);

        // Delete file if exists
        if ($izin->file_pendukung) {
            Storage::disk('public')->delete($izin->file_pendukung);
        }

        $izin->delete();

        return redirect()->route('guru.izin.index')
                        ->with('success', 'Permohonan izin/cuti dibatalkan.');
    }
}
