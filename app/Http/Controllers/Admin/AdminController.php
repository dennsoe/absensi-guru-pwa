<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Guru, Absensi, JadwalMengajar, Kelas};
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function dashboard()
    {
        $data = [
            'total_guru' => Guru::count(),
            'total_kelas' => Kelas::count(),
            'total_jadwal' => JadwalMengajar::where('status', 'aktif')->count(),
            'guru_hadir_hari_ini' => Absensi::whereDate('tanggal', today())
                                            ->where('status_kehadiran', 'hadir')
                                            ->count(),
            'guru_terlambat_hari_ini' => Absensi::whereDate('tanggal', today())
                                                ->where('status_kehadiran', 'terlambat')
                                                ->count(),
            'guru_izin_hari_ini' => Absensi::whereDate('tanggal', today())
                                           ->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti', 'dinas'])
                                           ->count(),
        ];

        return view('admin.dashboard', $data);
    }

    /**
     * Kelola User
     */
    public function users(Request $request)
    {
        $query = User::with(['guru', 'kelas'])->latest();

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(20)->withQueryString();
        $guru_list = Guru::all(); // For create/edit forms

        return view('admin.users.index', compact('users', 'guru_list'));
    }

    public function createUser()
    {
        $guru_list = Guru::whereDoesntHave('user')->get();
        $kelas_list = Kelas::all();
        return view('admin.users.create', compact('guru_list', 'kelas_list'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:50',
            'password' => 'required|string|min:6|confirmed',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,guru,ketua_kelas,guru_piket,kepala_sekolah,kurikulum',
            'guru_id' => 'nullable|exists:guru,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser(User $user)
    {
        $guru_list = Guru::whereDoesntHave('user')
                     ->orWhere('id', $user->guru_id)
                     ->get();
        $kelas_list = Kelas::all();
        return view('admin.users.edit', compact('user', 'guru_list', 'kelas_list'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,guru,ketua_kelas,guru_piket,kepala_sekolah,kurikulum',
            'guru_id' => 'nullable|exists:guru,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroyUser(User $user)
    {
        // Cek jika user adalah admin terakhir
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
