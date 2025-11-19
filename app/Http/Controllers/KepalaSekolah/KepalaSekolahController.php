<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{IzinCuti, Absensi, Guru, JadwalMengajar};
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;

class KepalaSekolahController extends Controller
{
    /**
     * Dashboard Executive - Kepala Sekolah
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role !== 'kepala_sekolah') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Hanya Kepala Sekolah yang dapat mengakses halaman ini.');
        }

        $bulan_ini = Carbon::now()->month;
        $tahun_ini = Carbon::now()->year;

        // STATISTIK BULAN INI
        $total_guru = Guru::whereHas('user', function($q) {
            $q;
        })->count();

        $total_absensi_bulan_ini = Absensi::whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->count();

        $hadir = Absensi::whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->where('status', 'hadir')
            ->count();

        $terlambat = Absensi::whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->where('status', 'terlambat')
            ->count();

        $izin_sakit = Absensi::whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->whereIn('status', ['izin', 'sakit'])
            ->count();

        $alpha = Absensi::whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->where('status', 'alpha')
            ->count();

        $persentase_kehadiran = $total_absensi_bulan_ini > 0
            ? round((($hadir + $terlambat) / $total_absensi_bulan_ini) * 100, 1)
            : 0;

        // PENDING APPROVAL IZIN/CUTI
        $pending_approval = IzinCuti::where('status', 'pending')->count();

        // CHART DATA: 30 HARI TERAKHIR
        $chart_data = [
            'labels' => [],
            'hadir' => [],
            'terlambat' => [],
            'izin_sakit' => [],
            'alpha' => [],
        ];

        for ($i = 29; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subDays($i);
            $chart_data['labels'][] = $tanggal->format('d/m');

            $chart_data['hadir'][] = Absensi::whereDate('tanggal', $tanggal)
                ->where('status', 'hadir')
                ->count();

            $chart_data['terlambat'][] = Absensi::whereDate('tanggal', $tanggal)
                ->where('status', 'terlambat')
                ->count();

            $chart_data['izin_sakit'][] = Absensi::whereDate('tanggal', $tanggal)
                ->whereIn('status', ['izin', 'sakit'])
                ->count();

            $chart_data['alpha'][] = Absensi::whereDate('tanggal', $tanggal)
                ->where('status', 'alpha')
                ->count();
        }

        // GURU DENGAN PELANGGARAN TERTINGGI (Alpha + Terlambat)
        $guru_pelanggaran = Guru::select('guru.*')
            ->with('user')
            ->selectRaw('(SELECT COUNT(*) FROM absensi INNER JOIN jadwal_mengajar ON absensi.jadwal_id = jadwal_mengajar.id WHERE jadwal_mengajar.guru_id = guru.id AND absensi.status = "alpha" AND MONTH(absensi.tanggal) = ? AND YEAR(absensi.tanggal) = ?) as jumlah_alpha', [$bulan_ini, $tahun_ini])
            ->selectRaw('(SELECT COUNT(*) FROM absensi INNER JOIN jadwal_mengajar ON absensi.jadwal_id = jadwal_mengajar.id WHERE jadwal_mengajar.guru_id = guru.id AND absensi.status = "terlambat" AND MONTH(absensi.tanggal) = ? AND YEAR(absensi.tanggal) = ?) as jumlah_terlambat', [$bulan_ini, $tahun_ini])
            ->get()
            ->map(function($guru) {
                $guru->total_pelanggaran = $guru->jumlah_alpha + $guru->jumlah_terlambat;
                return $guru;
            })
            ->where('total_pelanggaran', '>', 0)
            ->sortByDesc('total_pelanggaran')
            ->take(10)
            ->values();

        // RECENT ACTIVITIES (5 terbaru)
        $recent_activities = Absensi::with(['jadwal.guru.user', 'jadwal.kelas', 'jadwal.mataPelajaran'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('kepala-sekolah.dashboard', [
            'statistik' => [
                'total_guru' => $total_guru,
                'total_absensi' => $total_absensi_bulan_ini,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => $izin_sakit,
                'alpha' => $alpha,
                'persentase_kehadiran' => $persentase_kehadiran,
                'pending_approval' => $pending_approval,
            ],
            'chart_data' => $chart_data,
            'guru_pelanggaran' => $guru_pelanggaran,
            'recent_activities' => $recent_activities,
            'bulan' => Carbon::now()->locale('id')->monthName,
            'tahun' => $tahun_ini,
        ]);
    }

    /**
     * Halaman Approval Izin/Cuti
     */
    public function izinCuti(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kepala_sekolah') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $status = $request->input('status', 'all');

        $query = IzinCuti::with(['guru.user'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $izin_cuti = $query->paginate(20);

        $statistik = [
            'total' => IzinCuti::count(),
            'pending' => IzinCuti::where('status', 'pending')->count(),
            'approved' => IzinCuti::where('status', 'approved')->count(),
            'rejected' => IzinCuti::where('status', 'rejected')->count(),
        ];

        return view('kepala-sekolah.izin-cuti.index', [
            'izin_cuti' => $izin_cuti,
            'statistik' => $statistik,
            'status_filter' => $status,
        ]);
    }

    /**
     * Detail Izin/Cuti
     */
    public function showIzinCuti($id)
    {
        $user = Auth::user();

        if ($user->role !== 'kepala_sekolah') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $izin_cuti = IzinCuti::with(['guru.user'])->findOrFail($id);

        return view('kepala-sekolah.izin-cuti.show', [
            'izin_cuti' => $izin_cuti,
        ]);
    }

    /**
     * Approve Izin/Cuti
     */
    public function approveIzinCuti(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'kepala_sekolah') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Kepala Sekolah yang dapat menyetujui izin/cuti.',
                ], 403);
            }

            $request->validate([
                'catatan_approval' => 'nullable|string|max:500',
            ]);

            $izin_cuti = IzinCuti::findOrFail($id);

            if ($izin_cuti->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin/Cuti ini sudah diproses sebelumnya.',
                ], 400);
            }

            $izin_cuti->update([
                'status' => 'approved',
                'approved_by_kepala_sekolah_id' => $user->id,
                'approved_at' => Carbon::now(),
                'catatan_approval' => $request->catatan_approval ?? 'Disetujui oleh Kepala Sekolah',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Izin/Cuti berhasil disetujui.',
                'data' => [
                    'izin_cuti_id' => $izin_cuti->id,
                    'status' => 'approved',
                    'approved_by' => $user->name,
                    'approved_at' => $izin_cuti->approved_at->format('d M Y H:i'),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error approve izin/cuti: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject Izin/Cuti
     */
    public function rejectIzinCuti(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'kepala_sekolah') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Kepala Sekolah yang dapat menolak izin/cuti.',
                ], 403);
            }

            $request->validate([
                'alasan_penolakan' => 'required|string|max:500',
            ]);

            $izin_cuti = IzinCuti::findOrFail($id);

            if ($izin_cuti->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin/Cuti ini sudah diproses sebelumnya.',
                ], 400);
            }

            $izin_cuti->update([
                'status' => 'rejected',
                'approved_by_kepala_sekolah_id' => $user->id,
                'approved_at' => Carbon::now(),
                'alasan_penolakan' => $request->alasan_penolakan,
                'catatan_approval' => 'Ditolak: ' . $request->alasan_penolakan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Izin/Cuti berhasil ditolak.',
                'data' => [
                    'izin_cuti_id' => $izin_cuti->id,
                    'status' => 'rejected',
                    'rejected_by' => $user->name,
                    'approved_at' => $izin_cuti->approved_at->format('d M Y H:i'),
                    'alasan' => $request->alasan_penolakan,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reject izin/cuti: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Laporan Kehadiran per Guru
     */
    public function laporanKehadiran(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kepala_sekolah') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Laporan per Guru
        $laporan = Guru::with('user')
            ->whereHas('user', function($q) {
                $q;
            })
            ->get()
            ->map(function($guru) use ($bulan, $tahun) {
                $total_jadwal = JadwalMengajar::where('guru_id', $guru->id)
                    
                    ->count();

                $absensi_data = Absensi::whereHas('jadwal', function($q) use ($guru) {
                        $q->where('guru_id', $guru->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->selectRaw('
                        COUNT(*) as total_absensi,
                        SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as terlambat,
                        SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha
                    ')
                    ->first();

                $guru->statistik = [
                    'total_jadwal' => $total_jadwal,
                    'total_absensi' => $absensi_data->total_absensi ?? 0,
                    'hadir' => $absensi_data->hadir ?? 0,
                    'terlambat' => $absensi_data->terlambat ?? 0,
                    'izin' => $absensi_data->izin ?? 0,
                    'sakit' => $absensi_data->sakit ?? 0,
                    'alpha' => $absensi_data->alpha ?? 0,
                    'persentase_kehadiran' => ($absensi_data->total_absensi ?? 0) > 0
                        ? round(((($absensi_data->hadir ?? 0) + ($absensi_data->terlambat ?? 0)) / $absensi_data->total_absensi) * 100, 1)
                        : 0,
                ];

                return $guru;
            })
            ->sortByDesc(function($guru) {
                return $guru->statistik['alpha'] + $guru->statistik['terlambat'];
            })
            ->values();

        return view('kepala-sekolah.laporan.kehadiran', [
            'laporan' => $laporan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_nama' => Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName,
        ]);
    }

    /**
     * Laporan Kedisiplinan (Ranking Guru)
     */
    public function laporanKedisiplinan(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'kepala_sekolah') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Guru dengan Pelanggaran
        $laporan = Guru::with('user')
            ->whereHas('user', function($q) {
                $q;
            })
            ->get()
            ->map(function($guru) use ($bulan, $tahun) {
                $pelanggaran = Absensi::whereHas('jadwal', function($q) use ($guru) {
                        $q->where('guru_id', $guru->id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->whereIn('status', ['alpha', 'terlambat'])
                    ->selectRaw('
                        SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha,
                        SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as terlambat
                    ')
                    ->first();

                $guru->alpha = $pelanggaran->alpha ?? 0;
                $guru->terlambat = $pelanggaran->terlambat ?? 0;
                $guru->total_pelanggaran = $guru->alpha + $guru->terlambat;

                // Kategori Kedisiplinan
                if ($guru->total_pelanggaran === 0) {
                    $guru->kategori = 'Sangat Baik';
                    $guru->kategori_class = 'success';
                } elseif ($guru->total_pelanggaran <= 2) {
                    $guru->kategori = 'Baik';
                    $guru->kategori_class = 'info';
                } elseif ($guru->total_pelanggaran <= 5) {
                    $guru->kategori = 'Cukup';
                    $guru->kategori_class = 'warning';
                } else {
                    $guru->kategori = 'Perlu Perhatian';
                    $guru->kategori_class = 'danger';
                }

                return $guru;
            })
            ->sortByDesc('total_pelanggaran')
            ->values();

        return view('kepala-sekolah.laporan.kedisiplinan', [
            'laporan' => $laporan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_nama' => Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName,
        ]);
    }
}
