<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{IzinCuti, Absensi, Guru, SuratPeringatan, Laporan};
use Illuminate\Support\Facades\DB;

class KepalaSekolahController extends Controller
{
    /**
     * Dashboard Kepala Sekolah
     */
    public function dashboard()
    {
        // Persentase kehadiran bulan ini
        $total_absensi = Absensi::whereMonth('tanggal', now()->month)->count();
        $total_hadir = Absensi::whereMonth('tanggal', now()->month)
                              ->where('status_kehadiran', 'hadir')
                              ->count();
        $persentase_kehadiran = $total_absensi > 0 ? ($total_hadir / $total_absensi) * 100 : 0;

        // Pending approval
        $pending_approval = IzinCuti::where('status', 'pending')->count();

        // Total pelanggaran bulan ini
        $total_pelanggaran_bulan_ini = Absensi::whereMonth('tanggal', now()->month)
                                               ->whereIn('status_kehadiran', ['alpha', 'terlambat'])
                                               ->count();

        // Total guru
        $total_guru = Guru::count();

        // Guru dengan pelanggaran tertinggi
        $guru_pelanggaran = Guru::select('guru.*')
            ->selectRaw('(SELECT COUNT(*) FROM absensi WHERE absensi.guru_id = guru.guru_id AND status_kehadiran = "alpha" AND MONTH(tanggal) = ?) as alfa_count', [now()->month])
            ->selectRaw('(SELECT COUNT(*) FROM absensi WHERE absensi.guru_id = guru.guru_id AND status_kehadiran = "terlambat" AND MONTH(tanggal) = ?) as terlambat_count', [now()->month])
            ->get()
            ->map(function($guru) {
                $guru->total_pelanggaran = $guru->alfa_count + $guru->terlambat_count;
                return $guru;
            })
            ->where('total_pelanggaran', '>', 0)
            ->sortByDesc('total_pelanggaran')
            ->take(10);

        // Chart data (7 hari terakhir)
        $chart_labels = [];
        $chart_hadir = [];
        $chart_terlambat = [];
        $chart_alfa = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chart_labels[] = $date->format('d/m');
            $chart_hadir[] = Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'hadir')->count();
            $chart_terlambat[] = Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'terlambat')->count();
            $chart_alfa[] = Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'alpha')->count();
        }

        // Summary bulan ini
        $summary_bulan_ini = [
            'hadir' => Absensi::whereMonth('tanggal', now()->month)->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => Absensi::whereMonth('tanggal', now()->month)->where('status_kehadiran', 'terlambat')->count(),
            'izin' => Absensi::whereMonth('tanggal', now()->month)->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alfa' => Absensi::whereMonth('tanggal', now()->month)->where('status_kehadiran', 'alpha')->count(),
        ];

        return view('kepala-sekolah.dashboard', compact(
            'persentase_kehadiran',
            'pending_approval',
            'total_pelanggaran_bulan_ini',
            'total_guru',
            'guru_pelanggaran',
            'chart_labels',
            'chart_hadir',
            'chart_terlambat',
            'chart_alfa',
            'summary_bulan_ini'
        ));
    }

    /**
     * Approval Izin/Cuti
     */
    public function izinCuti()
    {
        $izin = IzinCuti::with('guru')->latest()->paginate(20);
        return view('kepsek.izin-cuti.index', compact('izin'));
    }

    public function approveIzin(IzinCuti $izin)
    {
        $izin->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth()->id(),
            'tanggal_disetujui' => now(),
        ]);

        return back()->with('success', 'Izin/Cuti berhasil disetujui.');
    }

    public function rejectIzin(Request $request, IzinCuti $izin)
    {
        $validated = $request->validate([
            'alasan_ditolak' => 'required|string|max:500',
        ]);

        $izin->update([
            'status' => 'ditolak',
            'disetujui_oleh' => auth()->id(),
            'tanggal_disetujui' => now(),
            'keterangan' => $validated['alasan_ditolak'],
        ]);

        return back()->with('success', 'Izin/Cuti berhasil ditolak.');
    }

    /**
     * Laporan Kedisiplinan
     */
    public function laporanKedisiplinan(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $laporan = Absensi::select(
                        'guru_id',
                        DB::raw('COUNT(*) as total_absensi'),
                        DB::raw('SUM(CASE WHEN status_kehadiran = "hadir" THEN 1 ELSE 0 END) as total_hadir'),
                        DB::raw('SUM(CASE WHEN status_kehadiran = "terlambat" THEN 1 ELSE 0 END) as total_terlambat'),
                        DB::raw('SUM(CASE WHEN status_kehadiran = "alpha" THEN 1 ELSE 0 END) as total_alpha')
                    )
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->groupBy('guru_id')
                    ->with('guru')
                    ->get();

        return view('kepsek.laporan.kedisiplinan', compact('laporan', 'bulan', 'tahun'));
    }
}
