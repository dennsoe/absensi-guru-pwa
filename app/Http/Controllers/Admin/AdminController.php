<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, Guru, Absensi, JadwalMengajar, Kelas, MataPelajaran, IzinCuti};
use Illuminate\Support\Facades\{Hash, Auth, DB, Log};
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Admin dengan System Overview
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        try {
            // System Statistics
            $stats = [
                'total_users' => User::count(),
                'total_guru' => Guru::count(),
                'total_kelas' => Kelas::count(),
                'total_mapel' => MataPelajaran::count(),
                'total_jadwal_aktif' => JadwalMengajar::where('status', 'aktif')->count(),
            ];

            // Today's Activity
            $today = Carbon::today();
            $activity_today = [
                'hadir' => Absensi::whereDate('tanggal', $today)
                    ->where('status_kehadiran', 'hadir')
                    ->count(),
                'terlambat' => Absensi::whereDate('tanggal', $today)
                    ->where('status_kehadiran', 'terlambat')
                    ->count(),
                'izin' => Absensi::whereDate('tanggal', $today)
                    ->whereIn('status_kehadiran', ['izin', 'sakit'])
                    ->count(),
                'alpha' => Absensi::whereDate('tanggal', $today)
                    ->where('status_kehadiran', 'alpha')
                    ->count(),
            ];

            // Pending Approvals
            $pending_izin = IzinCuti::where('status', 'pending')->count();

            // Recent Users (last 10)
            $recent_users = User::with('guru')
                ->latest()
                ->take(10)
                ->get();

            // Active Users by Role
            $users_by_role = User::select('role', DB::raw('count(*) as total'))
                ->where('status', 'aktif')
                ->groupBy('role')
                ->get()
                ->keyBy('role');

            // Prepare individual variables for view (backward compatibility)
            $total_guru = $stats['total_guru'];
            $total_kelas = $stats['total_kelas'];
            $total_jadwal = $stats['total_jadwal_aktif'];
            $guru_hadir_hari_ini = $activity_today['hadir'];
            $guru_terlambat_hari_ini = $activity_today['terlambat'];
            $guru_izin_hari_ini = $activity_today['izin'];

            return view('admin.dashboard', compact(
                'stats',
                'activity_today',
                'pending_izin',
                'recent_users',
                'users_by_role',
                'total_guru',
                'total_kelas',
                'total_jadwal',
                'guru_hadir_hari_ini',
                'guru_terlambat_hari_ini',
                'guru_izin_hari_ini'
            ));

        } catch (\Exception $e) {
            Log::error('Error admin dashboard: ' . $e->getMessage());
            return view('admin.dashboard')->with('error', 'Terjadi kesalahan saat memuat dashboard.');
        }
    }

    /**
     * ========================================
     * USER MANAGEMENT
     * ========================================
     */

    /**
     * List Users dengan Search & Filter
     */
    public function users(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = User::with(['guru', 'kelas']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by Status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Form Create User
     */
    public function createUser()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Ambil semua guru (izinkan 1 guru punya multiple user dengan role berbeda)
        $guru_list = Guru::orderBy('nama', 'asc')->get();
        $kelas_list = Kelas::all();

        return view('admin.users.create', compact('guru_list', 'kelas_list'));
    }

    /**
     * Store New User
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email',
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,guru,ketua_kelas,guru_piket,kepala_sekolah,kurikulum',
            'guru_id' => 'nullable|exists:guru,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'required|in:aktif,nonaktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            // Field tambahan untuk profil guru
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'status_kepegawaian' => 'nullable|in:PNS,PPPK,Honorer,GTY,GTT',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            // Validasi: jika role ketua_kelas, harus ada kelas_id
            if ($request->role === 'ketua_kelas' && !$request->kelas_id) {
                return back()->withErrors(['kelas_id' => 'Kelas harus dipilih untuk role Ketua Kelas.'])
                    ->withInput();
            }

            // Prepare user data (tanpa guru_id dulu)
            $userData = [
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'email' => $request->email,
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
                'role' => $request->role,
                'guru_id' => $request->guru_id, // Akan diupdate jika auto-create
                'kelas_id' => $request->kelas_id,
                'status' => $request->status,
            ];

            // Handle foto profil upload
            if ($request->hasFile('foto_profil')) {
                $file = $request->file('foto_profil');
                $filename = 'foto-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('foto-profil', $filename, 'public');
                $userData['foto_profil'] = $path;
            }

            // Buat user dulu
            $newUser = User::create($userData);

            // Auto-create profil guru SETELAH user dibuat (agar bisa set user_id)
            $rolesYangPerluGuru = ['guru', 'guru_piket', 'kepala_sekolah', 'kurikulum'];
            if (in_array($request->role, $rolesYangPerluGuru) && !$request->guru_id) {
                // Buat profil guru baru otomatis dengan data lengkap
                $guruBaru = Guru::create([
                    'user_id' => $newUser->id, // Link ke user yang baru dibuat
                    'nama' => $request->nama,
                    'nip' => $request->nip,
                    'email' => $request->email,
                    'no_hp' => $request->no_hp,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'alamat' => $request->alamat,
                    'status_kepegawaian' => $request->status_kepegawaian ?? 'Honorer',
                ]);

                // Update user dengan guru_id
                $newUser->update(['guru_id' => $guruBaru->id]);
            }

            return redirect()->route('admin.users')
                ->with('success', 'User berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Error store user: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form Edit User
     */
    public function editUser($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $user_edit = User::with(['guru', 'kelas'])->findOrFail($id);

        // Ambil semua guru (izinkan 1 guru punya multiple user dengan role berbeda)
        $guru_list = Guru::orderBy('nama', 'asc')->get();

        $kelas_list = Kelas::all();

        return view('admin.users.edit', [
            'user' => $user_edit,
            'guru_list' => $guru_list,
            'kelas_list' => $kelas_list,
        ]);
    }

    /**
     * Update User
     */
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'role' => 'required|in:admin,guru,ketua_kelas,guru_piket,kepala_sekolah,kurikulum',
            'guru_id' => 'nullable|exists:guru,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'required|in:aktif,nonaktif',
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            // Field tambahan untuk profil guru
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'status_kepegawaian' => 'nullable|in:PNS,PPPK,Honorer,GTY,GTT',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $user_edit = User::findOrFail($id);

            // Prepare data untuk update user
            $data = [
                'username' => $request->username,
                'nama' => $request->nama,
                'email' => $request->email,
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
                'role' => $request->role,
                'guru_id' => $request->guru_id ?: $user_edit->guru_id,
                'kelas_id' => $request->kelas_id,
                'status' => $request->status,
            ];

            // Handle profil guru untuk role yang memerlukan
            $rolesYangPerluGuru = ['guru', 'guru_piket', 'kepala_sekolah', 'kurikulum'];
            if (in_array($request->role, $rolesYangPerluGuru)) {
                $guru_id = $request->guru_id ?: $user_edit->guru_id;

                if ($guru_id) {
                    // Update profil guru yang sudah ada
                    $guru = Guru::find($guru_id);
                    if ($guru) {
                        $guru->update([
                            'nama' => $request->nama,
                            'nip' => $request->nip,
                            'email' => $request->email,
                            'no_hp' => $request->no_hp,
                            'jenis_kelamin' => $request->jenis_kelamin,
                            'tanggal_lahir' => $request->tanggal_lahir,
                            'alamat' => $request->alamat,
                            'status_kepegawaian' => $request->status_kepegawaian,
                        ]);
                    }
                    $data['guru_id'] = $guru_id;
                } else {
                    // Buat profil guru baru jika belum ada
                    $guruBaru = Guru::create([
                        'user_id' => $user_edit->id, // Link ke user yang sedang diedit
                        'nama' => $request->nama,
                        'nip' => $request->nip,
                        'email' => $request->email,
                        'no_hp' => $request->no_hp,
                        'jenis_kelamin' => $request->jenis_kelamin,
                        'tanggal_lahir' => $request->tanggal_lahir,
                        'alamat' => $request->alamat,
                        'status_kepegawaian' => $request->status_kepegawaian ?? 'Honorer',
                    ]);
                    $data['guru_id'] = $guruBaru->id;
                }
            }

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Handle foto profil upload
            if ($request->hasFile('foto_profil')) {
                // Hapus foto lama jika ada
                if ($user_edit->foto_profil && file_exists(storage_path('app/public/' . $user_edit->foto_profil))) {
                    unlink(storage_path('app/public/' . $user_edit->foto_profil));
                }

                // Upload foto baru
                $file = $request->file('foto_profil');
                $filename = 'foto-' . $user_edit->id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('foto-profil', $filename, 'public');
                $data['foto_profil'] = $path;
            }

            $user_edit->update($data);

            return redirect()->route('admin.users')
                ->with('success', 'User berhasil diupdate.');

        } catch (\Exception $e) {
            Log::error('Error update user: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete User
     */
    public function destroyUser($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $user_delete = User::findOrFail($id);

            // Cek jika user adalah admin terakhir
            if ($user_delete->role === 'admin') {
                $admin_count = User::where('role', 'admin')->count();
                if ($admin_count <= 1) {
                    return redirect()->route('admin.users')
                        ->with('error', 'Tidak dapat menghapus admin terakhir.');
                }
            }

            // Cek jika sedang menghapus diri sendiri
            if ($user_delete->id === $user->id) {
                return redirect()->route('admin.users')
                    ->with('error', 'Tidak dapat menghapus akun sendiri.');
            }

            $user_delete->delete();

            return redirect()->route('admin.users')
                ->with('success', 'User berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete user: ' . $e->getMessage());
            return redirect()->route('admin.users')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * GURU MANAGEMENT
     * ========================================
     */

    /**
     * List Guru
     */
    public function guru(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = Guru::with('user');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'created_desc');
        switch ($sort) {
            case 'nama_asc':
                $query->orderBy('nama', 'asc');
                break;
            case 'nama_desc':
                $query->orderBy('nama', 'desc');
                break;
            case 'nip_asc':
                $query->orderBy('nip', 'asc');
                break;
            case 'created_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $guru_list = $query->paginate(20);

        return view('admin.guru.index', [
            'guru_list' => $guru_list,
        ]);
    }

    /**
     * Form Create Guru
     */
    public function createGuru()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        return view('admin.guru.create');
    }

    /**
     * Store Guru
     */
    public function storeGuru(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:50|unique:guru,nip',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:guru,email',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            DB::beginTransaction();

            // Create User Account
            $user_guru = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'email' => $request->email,
                'role' => 'guru',
                'status' => 'aktif',
            ]);

            // Create Guru
            Guru::create([
                'user_id' => $user_guru->id,
                'nip' => $request->nip,
                'nama' => $request->nama,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('admin.guru')
                ->with('success', 'Data guru dan akun pengguna berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store guru: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form Edit Guru
     */
    public function editGuru($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $guru = Guru::with('user')->findOrFail($id);

        return view('admin.guru.edit', compact('guru'));
    }

    /**
     * Update Guru
     */
    public function updateGuru(Request $request, $id)
    {
        $request->validate([
            'nip' => 'required|string|max:50|unique:guru,nip,' . $id,
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|unique:guru,email,' . $id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $guru = Guru::findOrFail($id);

            DB::beginTransaction();

            // Update Guru
            $guru->update([
                'nip' => $request->nip,
                'nama' => $request->nama,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
                'status' => $request->status,
            ]);

            // Update User if exists and password provided
            if ($guru->user && $request->filled('password')) {
                $guru->user->update([
                    'password' => Hash::make($request->password),
                    'nama' => $request->nama,
                    'email' => $request->email,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.guru')
                ->with('success', 'Data guru berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update guru: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete Guru
     */
    public function destroyGuru($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $guru = Guru::findOrFail($id);

            // Cek jika ada user terkait
            if ($guru->user_id) {
                return redirect()->route('admin.guru')
                    ->with('error', 'Tidak dapat menghapus guru yang memiliki akun user. Hapus user terlebih dahulu.');
            }

            // Cek jika ada jadwal terkait
            $ada_jadwal = JadwalMengajar::where('guru_id', $id)->exists();
            if ($ada_jadwal) {
                return redirect()->route('admin.guru')
                    ->with('error', 'Tidak dapat menghapus guru yang memiliki jadwal mengajar.');
            }

            $guru->delete();

            return redirect()->route('admin.guru')
                ->with('success', 'Data guru berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete guru: ' . $e->getMessage());
            return redirect()->route('admin.guru')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * KELAS MANAGEMENT
     * ========================================
     */

    /**
     * List Kelas
     */
    public function kelas(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = Kelas::with(['waliKelas', 'ketuaKelas']);

        if ($request->has('search') && $request->search) {
            $query->where('nama_kelas', 'like', "%{$request->search}%");
        }

        $kelas_list = $query->orderBy('nama_kelas')
            ->paginate(20);

        return view('admin.kelas.index', [
            'kelas_list' => $kelas_list,
            'search' => $request->search,
        ]);
    }

    /**
     * Form Create Kelas
     */
    public function createKelas()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $guru_list = Guru::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'aktif');
            })
            ->get();

        $ketua_kelas_list = User::where('role', 'ketua_kelas')
            ->where('status', 'aktif')
            ->whereDoesntHave('kelas')
            ->get();

        return view('admin.kelas.create', compact('guru_list', 'ketua_kelas_list'));
    }

    /**
     * Store Kelas
     */
    public function storeKelas(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50|unique:kelas,nama_kelas',
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:50',
            'wali_kelas_guru_id' => 'nullable|exists:guru,id',
            'ketua_kelas_user_id' => 'nullable|exists:users,id',
            'tahun_ajaran' => 'nullable|string|max:20',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            Kelas::create($request->all());

            return redirect()->route('admin.kelas')
                ->with('success', 'Kelas berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Error store kelas: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form Edit Kelas
     */
    public function editKelas($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $kelas = Kelas::with(['waliKelas', 'ketuaKelas'])->findOrFail($id);

        $guru_list = Guru::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'aktif');
            })
            ->get();

        $ketua_kelas_list = User::where('role', 'ketua_kelas')
            ->where('status', 'aktif')
            ->where(function($q) use ($kelas) {
                $q->whereDoesntHave('kelas')
                  ->orWhere('id', $kelas->ketua_kelas_user_id);
            })
            ->get();

        return view('admin.kelas.edit', [
            'kelas' => $kelas,
            'guru_list' => $guru_list,
            'ketua_kelas_list' => $ketua_kelas_list,
        ]);
    }

    /**
     * Update Kelas
     */
    public function updateKelas(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50|unique:kelas,nama_kelas,' . $id,
            'tingkat' => 'required|integer|min:1|max:12',
            'jurusan' => 'nullable|string|max:50',
            'wali_kelas_guru_id' => 'nullable|exists:guru,id',
            'ketua_kelas_user_id' => 'nullable|exists:users,id',
            'tahun_ajaran' => 'nullable|string|max:20',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $kelas = Kelas::findOrFail($id);
            $kelas->update($request->all());

            return redirect()->route('admin.kelas')
                ->with('success', 'Kelas berhasil diupdate.');

        } catch (\Exception $e) {
            Log::error('Error update kelas: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete Kelas
     */
    public function destroyKelas($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $kelas = Kelas::findOrFail($id);

            // Cek jika ada jadwal terkait
            $ada_jadwal = JadwalMengajar::where('kelas_id', $id)->exists();
            if ($ada_jadwal) {
                return redirect()->route('admin.kelas')
                    ->with('error', 'Tidak dapat menghapus kelas yang memiliki jadwal mengajar.');
            }

            $kelas->delete();

            return redirect()->route('admin.kelas')
                ->with('success', 'Kelas berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete kelas: ' . $e->getMessage());
            return redirect()->route('admin.kelas')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * MATA PELAJARAN MANAGEMENT
     * ========================================
     */

    /**
     * List Mata Pelajaran
     */
    public function mataPelajaran(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $query = MataPelajaran::query();

        if ($request->has('search') && $request->search) {
            $query->where('nama_mapel', 'like', "%{$request->search}%")
                  ->orWhere('kode_mapel', 'like', "%{$request->search}%");
        }

        $mapel_list = $query->withCount('jadwalMengajar')
            ->orderBy('nama_mapel')
            ->paginate(20);

        return view('admin.mapel.index', [
            'mapel_list' => $mapel_list,
            'search' => $request->search,
        ]);
    }

    /**
     * Form Create Mata Pelajaran
     */
    public function createMataPelajaran()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        return view('admin.mapel.create');
    }

    /**
     * Store Mata Pelajaran
     */
    public function storeMataPelajaran(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
            'kategori' => 'nullable|in:umum,kejuruan,muatan_lokal',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            MataPelajaran::create($request->all());

            return redirect()->route('admin.mapel')
                ->with('success', 'Mata pelajaran berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Error store mapel: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form Edit Mata Pelajaran
     */
    public function editMataPelajaran($id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $mapel = MataPelajaran::findOrFail($id);

        return view('admin.mapel.edit', compact('mapel'));
    }

    /**
     * Update Mata Pelajaran
     */
    public function updateMataPelajaran(Request $request, $id)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,' . $id,
            'nama_mapel' => 'required|string|max:100',
            'kategori' => 'nullable|in:umum,kejuruan,muatan_lokal',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $mapel = MataPelajaran::findOrFail($id);
            $mapel->update($request->all());

            return redirect()->route('admin.mapel')
                ->with('success', 'Mata pelajaran berhasil diupdate.');

        } catch (\Exception $e) {
            Log::error('Error update mapel: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete Mata Pelajaran
     */
    public function destroyMataPelajaran($id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            $mapel = MataPelajaran::findOrFail($id);

            // Cek jika ada jadwal terkait
            $ada_jadwal = JadwalMengajar::where('mapel_id', $id)->exists();
            if ($ada_jadwal) {
                return redirect()->route('admin.mapel')
                    ->with('error', 'Tidak dapat menghapus mata pelajaran yang memiliki jadwal mengajar.');
            }

            $mapel->delete();

            return redirect()->route('admin.mapel')
                ->with('success', 'Mata pelajaran berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error delete mapel: ' . $e->getMessage());
            return redirect()->route('admin.mapel')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * SYSTEM SETTINGS & ACTIVITY LOG
     * ========================================
     */

    /**
     * System Settings
     */
    public function settings()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Load settings dari database
        $pengaturan = \App\Models\PengaturanSistem::pluck('value', 'key')->toArray();

        $settings = [
            'school_name' => $pengaturan['school_name'] ?? 'SMK Negeri Kasomalang',
            'school_address' => $pengaturan['school_address'] ?? '',
            'school_year' => $pengaturan['school_year'] ?? '2024/2025',
            'school_latitude' => $pengaturan['school_latitude'] ?? '-6.200000',
            'school_longitude' => $pengaturan['school_longitude'] ?? '106.816666',
            'gps_radius' => $pengaturan['gps_radius'] ?? 200,
            'toleransi_terlambat' => $pengaturan['toleransi_terlambat'] ?? 15,
            'qr_expiry_minutes' => $pengaturan['qr_expiry_minutes'] ?? 15,
            'enable_selfie' => $pengaturan['enable_selfie'] ?? true,
            'enable_qr' => $pengaturan['enable_qr'] ?? true,
            'updated_at' => now()->format('d M Y H:i'),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update System Settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:100',
            'school_address' => 'nullable|string',
            'school_year' => 'nullable|string|max:20',
            'school_latitude' => 'required|numeric',
            'school_longitude' => 'required|numeric',
            'gps_radius' => 'required|integer|min:50|max:1000',
            'toleransi_terlambat' => 'required|integer|min:0|max:60',
            'qr_expiry_minutes' => 'required|integer|min:5|max:60',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak.');
            }

            DB::beginTransaction();

            // Update or create settings di database
            $settingsToUpdate = [
                'school_name' => $request->school_name,
                'school_address' => $request->school_address,
                'school_year' => $request->school_year,
                'school_latitude' => $request->school_latitude,
                'school_longitude' => $request->school_longitude,
                'gps_radius' => $request->gps_radius,
                'toleransi_terlambat' => $request->toleransi_terlambat,
                'qr_expiry_minutes' => $request->qr_expiry_minutes,
                'enable_selfie' => $request->has('enable_selfie') ? 1 : 0,
                'enable_qr' => $request->has('enable_qr') ? 1 : 0,
            ];

            foreach ($settingsToUpdate as $key => $value) {
                \App\Models\PengaturanSistem::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value ?? '',
                        'kategori' => 'sistem',
                        'tipe_data' => is_numeric($value) ? 'number' : 'string',
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.settings')
                ->with('success', 'Pengaturan sistem berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update settings: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Activity Logs
     */
    public function activityLog(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Date range filter
        $start_date = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        // Query absensi sebagai activity log
        $query = Absensi::with(['guru', 'jadwal.mataPelajaran', 'jadwal.kelas'])
            ->whereBetween('tanggal', [$start_date, $end_date]);

        // Type filter (metode absensi)
        if ($request->has('type') && $request->type) {
            $query->where('metode', $request->type);
        }

        // Role filter (based on guru)
        if ($request->has('role') && $request->role) {
            $query->whereHas('guru.user', function($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate(30);

        // Statistics for the period
        $stats = [
            'login' => 0, // Placeholder - implement if you have login logs
            'create' => Absensi::whereBetween('tanggal', [$start_date, $end_date])->count(),
            'update' => 0, // Placeholder
            'delete' => 0, // Placeholder
        ];

        // Transform absensi to activity format for view
        $activities->transform(function($item) {
            $item->type = $item->metode ?? 'manual';
            $item->description = "{$item->guru->nama} - {$item->jadwal->mataPelajaran->nama} ({$item->status})";
            $item->user = $item->guru->user ?? (object)['nama' => $item->guru->nama, 'username' => '-', 'role' => 'guru'];
            $item->ip_address = $item->ip_address ?? '-';
            $item->user_agent = $item->user_agent ?? '-';
            $item->details = json_encode([
                'status' => $item->status,
                'metode' => $item->metode,
                'kelas' => $item->jadwal->kelas->nama_kelas ?? '-',
                'mapel' => $item->jadwal->mataPelajaran->nama ?? '-',
            ]);
            return $item;
        });

        return view('admin.activity-log', compact('activities', 'stats'));
    }

    /**
     * Rekap/Monitoring Absensi
     */
    public function rekapAbsensi(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        // Tanggal filter (default hari ini)
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $guru_id = $request->input('guru_id');
        $kelas_id = $request->input('kelas_id');
        $status = $request->input('status');

        // Query absensi
        $query = Absensi::with(['guru', 'jadwal.kelas', 'jadwal.mataPelajaran'])
            ->where('tanggal', $tanggal);

        if ($guru_id) {
            $query->where('guru_id', $guru_id);
        }

        if ($kelas_id) {
            $query->whereHas('jadwal', function($q) use ($kelas_id) {
                $q->where('kelas_id', $kelas_id);
            });
        }

        if ($status) {
            $query->where('status_kehadiran', $status);
        }

        $absensi_list = $query->orderBy('jam_masuk', 'desc')->get();

        // Statistik hari ini
        $stats = [
            'total_jadwal' => JadwalMengajar::where('hari', strtolower(Carbon::parse($tanggal)->translatedFormat('l')))->count(),
            'total_absen' => Absensi::where('tanggal', $tanggal)->count(),
            'hadir' => Absensi::where('tanggal', $tanggal)->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => Absensi::where('tanggal', $tanggal)->where('status_kehadiran', 'terlambat')->count(),
            'izin' => Absensi::where('tanggal', $tanggal)->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti', 'dinas'])->count(),
            'alpha' => Absensi::where('tanggal', $tanggal)->where('status_kehadiran', 'alpha')->count(),
        ];

        $stats['belum_absen'] = $stats['total_jadwal'] - $stats['total_absen'];

        // List untuk filter
        $guru_list = Guru::orderBy('nama')->get();
        $kelas_list = Kelas::orderBy('nama_kelas')->get();

        return view('admin.absensi.rekap', compact('absensi_list', 'stats', 'tanggal', 'guru_list', 'kelas_list'));
    }
}
