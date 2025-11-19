<?php

namespace App\Http\Controllers\KetuaKelas;

use App\Http\Controllers\Controller;
use App\Models\{Absensi, JadwalMengajar, QrCode, Kelas, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class KetuaKelasController extends Controller
{
    /**
     * Dashboard Ketua Kelas
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil kelas dari user ketua kelas
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return redirect()->route('login')
                ->with('error', 'Anda tidak terdaftar sebagai ketua kelas.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
        $tanggal_hari_ini = Carbon::today();

        // Jadwal kelas hari ini
        $jadwal_kelas_hari_ini = JadwalMengajar::where('kelas_id', $kelas->id)
            ->where('hari', $hari_ini)
            
            ->with(['guru', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        // Tambahkan status absensi untuk setiap jadwal
        $jadwal_kelas_hari_ini->each(function($jadwal) use ($tanggal_hari_ini) {
            $jadwal->absensi_record = Absensi::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal_hari_ini)
                ->first();
        });

        // Statistik scan QR hari ini
        $total_scan_hari_ini = Absensi::whereDate('tanggal', $tanggal_hari_ini)
            ->where('metode_absensi', 'qr_code')
            ->where('ketua_kelas_user_id', $user->id)
            ->count();

        // Statistik selfie yang perlu validasi
        $selfie_perlu_validasi = Absensi::whereDate('tanggal', $tanggal_hari_ini)
            ->where('metode_absensi', 'selfie')
            ->where('validasi_ketua_kelas', false)
            ->whereHas('jadwal', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas->id);
            })
            ->count();

        // Guru yang sudah absen hari ini (untuk kelas ini)
        $guru_sudah_absen = Absensi::whereDate('tanggal', $tanggal_hari_ini)
            ->whereHas('jadwal', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas->id);
            })
            ->distinct('guru_id')
            ->count();

        // Total guru mengajar hari ini (di kelas ini)
        $total_guru_mengajar = $jadwal_kelas_hari_ini->unique('guru_id')->count();

        return view('ketua-kelas.dashboard', [
            'kelas' => $kelas,
            'hari_ini' => $hari_ini,
            'jadwal_kelas_hari_ini' => $jadwal_kelas_hari_ini,
            'total_scan_hari_ini' => $total_scan_hari_ini,
            'selfie_perlu_validasi' => $selfie_perlu_validasi,
            'guru_sudah_absen' => $guru_sudah_absen,
            'total_guru_mengajar' => $total_guru_mengajar,
        ]);
    }

    /**
     * Halaman Generate QR Code untuk Absensi
     */
    public function generateQr()
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return redirect()->route('ketua-kelas.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai ketua kelas.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);

        // Ambil jadwal kelas hari ini
        $jadwal_tersedia = JadwalMengajar::where('kelas_id', $kelas->id)
            ->where('hari', $hari_ini)
            
            ->with(['guru', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        // Ambil QR Code aktif yang sudah di-generate
        $qr_code_aktif = QrCode::where('used_by_ketua_kelas', $user->id)
            ->where('expired_at', '>', Carbon::now())
            ->where('is_used', false)
            ->with(['jadwal.guru', 'jadwal.mataPelajaran', 'jadwal.kelas'])
            ->orderBy('created_at', 'desc')
            ->first();

        return view('ketua-kelas.generate-qr', [
            'kelas' => $kelas,
            'jadwal_tersedia' => $jadwal_tersedia,
            'qr_code_aktif' => $qr_code_aktif,
        ]);
    }

    /**
     * Generate QR Code untuk jadwal tertentu (AJAX)
     */
    public function storeQrCode(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
        ]);

        $user = Auth::user();
        $jadwal = JadwalMengajar::findOrFail($validated['jadwal_id']);

        // Validasi: Pastikan jadwal adalah untuk kelas ketua kelas ini
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();
        if ($jadwal->kelas_id !== $kelas->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk jadwal ini.',
            ], 403);
        }

        // Nonaktifkan QR Code lama yang masih aktif untuk user ini
        QrCode::where('used_by_ketua_kelas', $user->id)
            ->where('expired_at', '>', Carbon::now())
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => Carbon::now()]);

        // Generate QR Code baru
        $qr_data = Str::uuid()->toString();
        $expired_at = Carbon::now()->addMinutes(15); // QR Code berlaku 15 menit

        $qr_code = QrCode::create([
            'guru_id' => $jadwal->guru_id,
            'jadwal_id' => $jadwal->id,
            'qr_data' => $qr_data,
            'expired_at' => $expired_at,
            'is_used' => false,
            'used_by_ketua_kelas' => $user->id,
        ]);

        // Generate QR Code Image (SVG)
        $qr_image = QrCodeGenerator::size(300)
            ->format('svg')
            ->generate($qr_data);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil di-generate.',
            'data' => [
                'qr_id' => $qr_code->id,
                'qr_data' => $qr_data,
                'qr_image' => $qr_image,
                'expired_at' => $expired_at->format('H:i:s'),
                'jadwal' => [
                    'guru' => $jadwal->guru->nama,
                    'mata_pelajaran' => $jadwal->mataPelajaran->nama_mapel,
                    'jam' => Carbon::parse($jadwal->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->jam_selesai)->format('H:i'),
                ],
            ],
        ]);
    }

    /**
     * Halaman Validasi Selfie Guru
     */
    public function validasiSelfie()
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return redirect()->route('ketua-kelas.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai ketua kelas.');
        }

        // Ambil selfie yang perlu divalidasi (untuk kelas ini)
        $selfie_pending = Absensi::where('metode_absensi', 'selfie')
            ->where('validasi_ketua_kelas', false)
            ->whereHas('jadwal', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas->id);
            })
            ->with(['guru', 'jadwal.mataPelajaran', 'jadwal.kelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ketua-kelas.validasi-selfie', [
            'kelas' => $kelas,
            'selfie_pending' => $selfie_pending,
        ]);
    }

    /**
     * Approve Selfie Absensi (AJAX)
     */
    public function approveSelfie(Request $request, Absensi $absensi)
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        // Validasi: Pastikan absensi untuk kelas ketua kelas ini
        if ($absensi->jadwal->kelas_id !== $kelas->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk validasi ini.',
            ], 403);
        }

        $absensi->update([
            'validasi_ketua_kelas' => true,
            'ketua_kelas_user_id' => $user->id,
            'waktu_validasi_ketua' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Selfie absensi berhasil divalidasi.',
        ]);
    }

    /**
     * Reject Selfie Absensi (AJAX)
     */
    public function rejectSelfie(Request $request, Absensi $absensi)
    {
        $validated = $request->validate([
            'keterangan' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        // Validasi: Pastikan absensi untuk kelas ketua kelas ini
        if ($absensi->jadwal->kelas_id !== $kelas->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk validasi ini.',
            ], 403);
        }

        $absensi->update([
            'validasi_ketua_kelas' => false,
            'ketua_kelas_user_id' => $user->id,
            'waktu_validasi_ketua' => Carbon::now(),
            'keterangan' => $validated['keterangan'],
            'status_kehadiran' => 'alpha', // Set status ke alpha jika ditolak
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Selfie absensi ditolak.',
        ]);
    }

    /**
     * Riwayat Absensi Kelas (History page)
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return redirect()->route('ketua-kelas.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai ketua kelas.');
        }

        $query = Absensi::whereHas('jadwal', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas->id);
            })
            ->with(['guru', 'jadwal.mataPelajaran']);

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // Filter metode absensi
        if ($request->filled('metode')) {
            $query->where('metode_absensi', $request->metode);
        }

        // Filter status kehadiran
        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }

        $riwayat = $query->latest('tanggal')
            ->latest('jam_masuk')
            ->paginate(20)
            ->withQueryString();

        return view('ketua-kelas.riwayat', [
            'kelas' => $kelas,
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Statistik Kelas (untuk AJAX/API)
     */
    public function statistik(Request $request)
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan.',
            ], 404);
        }

        $tanggal = $request->get('tanggal', Carbon::today());

        $statistik = [
            'total_jadwal' => JadwalMengajar::where('kelas_id', $kelas->id)
                ->where('hari', ucfirst(Carbon::parse($tanggal)->locale('id')->dayName))
                
                ->count(),

            'total_absensi' => Absensi::whereDate('tanggal', $tanggal)
                ->whereHas('jadwal', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->id);
                })
                ->count(),

            'hadir' => Absensi::whereDate('tanggal', $tanggal)
                ->where('status_kehadiran', 'hadir')
                ->whereHas('jadwal', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->id);
                })
                ->count(),

            'terlambat' => Absensi::whereDate('tanggal', $tanggal)
                ->where('status_kehadiran', 'terlambat')
                ->whereHas('jadwal', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->id);
                })
                ->count(),

            'izin' => Absensi::whereDate('tanggal', $tanggal)
                ->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])
                ->whereHas('jadwal', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->id);
                })
                ->count(),

            'alpha' => Absensi::whereDate('tanggal', $tanggal)
                ->where('status_kehadiran', 'alpha')
                ->whereHas('jadwal', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->id);
                })
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistik,
        ]);
    }

    /**
     * Jadwal Kelas Hari Ini (untuk AJAX/API)
     */
    public function jadwal()
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan.',
            ], 404);
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);

        $jadwal = JadwalMengajar::where('kelas_id', $kelas->id)
            ->where('hari', $hari_ini)
            
            ->with(['guru', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get()
            ->map(function($item) {
                // Cek status absensi
                $absensi = Absensi::where('jadwal_id', $item->id)
                    ->whereDate('tanggal', Carbon::today())
                    ->first();

                return [
                    'id' => $item->id,
                    'mata_pelajaran' => $item->mataPelajaran->nama_mapel,
                    'guru' => $item->guru->nama,
                    'jam_mulai' => Carbon::parse($item->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($item->jam_selesai)->format('H:i'),
                    'ruangan' => $item->ruangan,
                    'sudah_absen' => $absensi !== null,
                    'status_kehadiran' => $absensi ? $absensi->status_kehadiran : null,
                    'metode_absensi' => $absensi ? $absensi->metode_absensi : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $jadwal,
        ]);
    }

    /**
     * Halaman Validasi Absensi
     */
    public function validasi(Request $request)
    {
        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return redirect()->route('ketua-kelas.dashboard')
                ->with('error', 'Anda tidak terdaftar sebagai ketua kelas.');
        }

        $tanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));

        // Query absensi untuk kelas ini
        $query = Absensi::whereHas('jadwalMengajar', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })
        ->whereDate('tanggal', $tanggal)
        ->with(['guru', 'jadwalMengajar']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('validasi_ketua', $request->status);
        }

        // Filter by search (guru name)
        if ($request->filled('search')) {
            $query->whereHas('guru', function($q) use ($request) {
                $q->where('nama', 'LIKE', '%' . $request->search . '%');
            });
        }

        $absensiList = $query->latest('jam_masuk')->paginate(15);

        // Statistik
        $totalAbsensi = Absensi::whereHas('jadwalMengajar', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })
        ->whereDate('tanggal', $tanggal)
        ->count();

        $pendingCount = Absensi::whereHas('jadwalMengajar', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })
        ->whereDate('tanggal', $tanggal)
        ->where('validasi_ketua', 'pending')
        ->count();

        $validatedCount = Absensi::whereHas('jadwalMengajar', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })
        ->whereDate('tanggal', $tanggal)
        ->where('validasi_ketua', 'validated')
        ->count();

        $rejectedCount = Absensi::whereHas('jadwalMengajar', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })
        ->whereDate('tanggal', $tanggal)
        ->where('validasi_ketua', 'rejected')
        ->count();

        return view('ketua-kelas.validasi', [
            'absensiList' => $absensiList,
            'totalAbsensi' => $totalAbsensi,
            'pendingCount' => $pendingCount,
            'validatedCount' => $validatedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    /**
     * Update Status Validasi Absensi
     */
    public function validasiUpdate(Request $request)
    {
        $request->validate([
            'absensi_id' => 'required|exists:absensis,id',
            'status' => 'required|in:pending,validated,rejected',
        ]);

        $user = Auth::user();
        $kelas = Kelas::where('ketua_kelas_user_id', $user->id)->first();

        if (!$kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar sebagai ketua kelas'
            ], 403);
        }

        $absensi = Absensi::findOrFail($request->absensi_id);

        // Validasi absensi untuk kelas ini
        if ($absensi->jadwalMengajar->kelas_id !== $kelas->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk validasi absensi ini'
            ], 403);
        }

        $absensi->validasi_ketua = $request->status;
        $absensi->ketua_kelas_user_id = $user->id;
        $absensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Status validasi berhasil diupdate'
        ]);
    }
}
