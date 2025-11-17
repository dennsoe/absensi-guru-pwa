<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar, Kelas, IzinCuti};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Monitoring Dashboard Executive
     */
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'hari-ini');

        // Tentukan range tanggal berdasarkan periode
        switch ($periode) {
            case 'minggu-ini':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                break;
            case 'bulan-ini':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            default:
                $start = today();
                $end = today();
        }

        // Statistik Kehadiran
        $total_absensi = Absensi::whereBetween('tanggal', [$start, $end])->count();
        $stats = [
            'total' => $total_absensi,
            'hadir' => Absensi::whereBetween('tanggal', [$start, $end])->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => Absensi::whereBetween('tanggal', [$start, $end])->where('status_kehadiran', 'terlambat')->count(),
            'izin' => Absensi::whereBetween('tanggal', [$start, $end])->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => Absensi::whereBetween('tanggal', [$start, $end])->where('status_kehadiran', 'alpha')->count(),
        ];

        $stats['persentase_kehadiran'] = $total_absensi > 0
            ? round(($stats['hadir'] / $total_absensi) * 100, 1)
            : 0;

        // Guru dengan pelanggaran tertinggi
        $guru_pelanggaran = Guru::select('guru.*')
            ->selectRaw('(SELECT COUNT(*) FROM absensi WHERE absensi.guru_id = guru.id AND status_kehadiran = "alpha" AND tanggal BETWEEN ? AND ?) as alpha_count', [$start, $end])
            ->selectRaw('(SELECT COUNT(*) FROM absensi WHERE absensi.guru_id = guru.id AND status_kehadiran = "terlambat" AND tanggal BETWEEN ? AND ?) as terlambat_count', [$start, $end])
            ->get()
            ->map(function($guru) {
                $guru->total_pelanggaran = $guru->alpha_count + $guru->terlambat_count;
                return $guru;
            })
            ->where('total_pelanggaran', '>', 0)
            ->sortByDesc('total_pelanggaran')
            ->take(10);

        // Trend kehadiran (7 hari terakhir)
        $trend_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $trend_data[] = [
                'tanggal' => $date->format('d M'),
                'hadir' => Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'terlambat')->count(),
                'alpha' => Absensi::whereDate('tanggal', $date)->where('status_kehadiran', 'alpha')->count(),
            ];
        }

        // Izin pending approval
        $izin_pending = IzinCuti::where('status', 'pending')
                                ->with('guru')
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();

        return view('kepala-sekolah.monitoring.index', compact(
            'stats',
            'guru_pelanggaran',
            'trend_data',
            'izin_pending',
            'periode',
            'start',
            'end'
        ));
    }

    /**
     * Monitoring Real-time (AJAX)
     */
    public function realtime()
    {
        $hari_ini = ucfirst(now()->locale('id')->dayName);

        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                        ->where('status', 'aktif')
                                        ->count();

        $absensi_hari_ini = Absensi::whereDate('tanggal', today())->count();

        $stats = [
            'total_jadwal' => $jadwal_hari_ini,
            'sudah_absen' => $absensi_hari_ini,
            'belum_absen' => $jadwal_hari_ini - $absensi_hari_ini,
            'hadir' => Absensi::whereDate('tanggal', today())->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => Absensi::whereDate('tanggal', today())->where('status_kehadiran', 'terlambat')->count(),
            'izin' => Absensi::whereDate('tanggal', today())->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => Absensi::whereDate('tanggal', today())->where('status_kehadiran', 'alpha')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    /**
     * Detail Monitoring Per Kelas
     */
    public function perKelas($kelasId)
    {
        $kelas = Kelas::with('waliKelas')->findOrFail($kelasId);

        $absensi = Absensi::whereHas('jadwal', function($q) use ($kelasId) {
                            $q->where('kelas_id', $kelasId);
                        })
                        ->whereMonth('tanggal', now()->month)
                        ->with(['guru', 'jadwal.mataPelajaran'])
                        ->orderBy('tanggal', 'desc')
                        ->get();

        $stats = [
            'total' => $absensi->count(),
            'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensi->whereIn('status_kehadiran', ['izin', 'sakit'])->count(),
            'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
        ];

        return view('kepala-sekolah.monitoring.per-kelas', compact('kelas', 'absensi', 'stats'));
    }
}
