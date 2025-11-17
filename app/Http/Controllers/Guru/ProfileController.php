<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;
use Illuminate\Support\Facades\{Auth, Hash};

class ProfileController extends Controller
{
    /**
     * Profile Guru
     */
    public function index()
    {
        $guru = Guru::with('user')->where('user_id', Auth::id())->firstOrFail();

        // Get statistics
        $total_jadwal = $guru->jadwalMengajar()
                            ->where('status', 'aktif')
                            ->where('tahun_ajaran', '2025/2026')
                            ->count();

        $bulan_ini = now()->format('Y-m');
        $stats_bulan_ini = $guru->absensi()
                               ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan_ini])
                               ->selectRaw('
                                   COUNT(*) as total,
                                   SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
                                   SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as terlambat,
                                   SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin,
                                   SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha
                               ')
                               ->first();

        return view('guru.profile.index', compact('guru', 'total_jadwal', 'stats_bulan_ini'));
    }

    /**
     * Form Edit Profile
     */
    public function edit()
    {
        $guru = Guru::with('user')->where('user_id', Auth::id())->firstOrFail();

        return view('guru.profile.edit', compact('guru'));
    }

    /**
     * Update Profile
     */
    public function update(Request $request)
    {
        $guru = Guru::with('user')->where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $guru->user_id,
            'nip' => 'required|string|max:50|unique:guru,nip,' . $guru->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Update user data
        $userData = ['email' => $validated['email'], 'nama' => $validated['nama']];

        // Handle foto upload
        if ($request->hasFile('foto_profil')) {
            // Delete old photo if exists
            if ($guru->user->foto_profil && file_exists(storage_path('app/public/' . $guru->user->foto_profil))) {
                unlink(storage_path('app/public/' . $guru->user->foto_profil));
            }

            // Save new photo
            $file = $request->file('foto_profil');
            $filename = 'foto-' . $guru->user_id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto-profil', $filename, 'public');
            $userData['foto_profil'] = $path;
        }

        $guru->user->update($userData);

        // Update guru data
        $guru->update([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
        ]);

        return redirect()->route('guru.profile.index')
                        ->with('success', 'Profile berhasil diupdate.');
    }

    /**
     * Form Change Password
     */
    public function changePassword()
    {
        return view('guru.profile.change-password');
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return redirect()->route('guru.profile.index')
                        ->with('success', 'Password berhasil diubah.');
    }
}
