<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Analytics Dashboard
     */
    public function index()
    {
        // Trend 30 hari terakhir
        $trend_30_hari = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $trend_30_hari[] = [
                'tanggal' => $date->format('d M'),
                'hadir' => Absensi::whereDate('tanggal', $date)->where('status', 'hadir')->count(),
                'terlambat' => Absensi::whereDate('tanggal', $date)->where('status', 'terlambat')->count(),
                'alpha' => Absensi::whereDate('tanggal', $date)->where('status', 'alpha')->count(),
            ];
        }

        // Perbandingan bulanan (6 bulan terakhir)
        $perbandingan_bulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $bulan = $date->month;
            $tahun = $date->year;
            
            $total = Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count();
            $hadir = Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'hadir')->count();
            
            $perbandingan_bulanan[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'total' => $total,
                'hadir' => $hadir,
                'persentase' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        }

        // Top 10 Guru Terbaik (kehadiran tertinggi bulan ini)
        $guru_terbaik = Guru::withCount([
            'absensi as total' => function($q) {
                $q->whereMonth('tanggal', now()->month);
            },
            'absensi as hadir' => function($q) {
                $q->whereMonth('tanggal', now()->month)->where('status', 'hadir');
            },
        ])->get()
          ->map(function($guru) {
              $guru->persentase = $guru->total > 0 ? round(($guru->hadir / $guru->total) * 100, 1) : 0;
              return $guru;
          })
          ->sortByDesc('persentase')
          ->take(10);

        // Guru dengan pelanggaran (bulan ini)
        $guru_pelanggaran = Guru::withCount([
            'absensi as terlambat' => function($q) {
                $q->whereMonth('tanggal', now()->month)->where('status', 'terlambat');
            },
            'absensi as alpha' => function($q) {
                $q->whereMonth('tanggal', now()->month)->where('status', 'alpha');
            },
        ])->get()
          ->map(function($guru) {
              $guru->total_pelanggaran = $guru->terlambat + $guru->alpha;
              return $guru;
          })
          ->where('total_pelanggaran', '>', 0)
          ->sortByDesc('total_pelanggaran')
          ->take(10);

        // Statistik per hari dalam seminggu
        $stats_per_hari = DB::table('absensi')
            ->join('jadwal_mengajar', 'absensi.jadwal_id', '=', 'jadwal_mengajar.id')
            ->select('jadwal_mengajar.hari', DB::raw('COUNT(*) as total'))
            ->whereMonth('absensi.tanggal', now()->month)
            ->where('absensi.status', 'hadir')
            ->groupBy('jadwal_mengajar.hari')
            ->get()
            ->pluck('total', 'hari');

        return view('kepala-sekolah.analytics.index', compact(
            'trend_30_hari',
            'perbandingan_bulanan',
            'guru_terbaik',
            'guru_pelanggaran',
            'stats_per_hari'
        ));
    }

    /**
     * Analytics Per Guru
     */
    public function perGuru($guruId)
    {
        $guru = Guru::findOrFail($guruId);
        
        // Trend 6 bulan terakhir
        $trend_guru = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $bulan = $date->month;
            $tahun = $date->year;
            
            $absensi = Absensi::where('guru_id', $guruId)
                             ->whereMonth('tanggal', $bulan)
                             ->whereYear('tanggal', $tahun)
                             ->get();
            
            $trend_guru[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'total' => $absensi->count(),
                'hadir' => $absensi->where('status', 'hadir')->count(),
                'terlambat' => $absensi->where('status', 'terlambat')->count(),
                'alpha' => $absensi->where('status', 'alpha')->count(),
            ];
        }

        return view('kepala-sekolah.analytics.per-guru', compact('guru', 'trend_guru'));
    }
}
