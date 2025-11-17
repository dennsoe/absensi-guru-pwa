<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, Kelas, JadwalMengajar};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanEksekutifController extends Controller
{
    /**
     * Laporan Eksekutif Dashboard
     */
    public function index()
    {
        return view('kepala-sekolah.laporan.index');
    }

    /**
     * Laporan Bulanan
     */
    public function bulanan(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // Rekap per guru
        $rekap_guru = Guru::withCount([
            'absensi as total' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            },
            'absensi as hadir' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'hadir');
            },
            'absensi as terlambat' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'terlambat');
            },
            'absensi as izin' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->whereIn('status', ['izin', 'sakit']);
            },
            'absensi as alpha' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'alpha');
            },
        ])->get();

        // Summary
        $summary = [
            'total_guru' => Guru::count(),
            'total_absensi' => Absensi::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count(),
            'persentase_kehadiran' => 0,
            'total_pelanggaran' => Absensi::whereMonth('tanggal', $bulan)
                                          ->whereYear('tanggal', $tahun)
                                          ->whereIn('status', ['alpha', 'terlambat'])
                                          ->count(),
        ];

        if ($summary['total_absensi'] > 0) {
            $hadir = Absensi::whereMonth('tanggal', $bulan)
                           ->whereYear('tanggal', $tahun)
                           ->where('status', 'hadir')
                           ->count();
            $summary['persentase_kehadiran'] = round(($hadir / $summary['total_absensi']) * 100, 1);
        }

        return view('kepala-sekolah.laporan.bulanan', compact('rekap_guru', 'summary', 'bulan', 'tahun'));
    }

    /**
     * Export PDF Laporan Bulanan
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $rekap_guru = Guru::withCount([
            'absensi as total' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            },
            'absensi as hadir' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'hadir');
            },
            'absensi as terlambat' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'terlambat');
            },
            'absensi as izin' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->whereIn('status', ['izin', 'sakit']);
            },
            'absensi as alpha' => function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'alpha');
            },
        ])->get();

        $pdf = Pdf::loadView('kepala-sekolah.laporan.pdf', compact('rekap_guru', 'bulan', 'tahun'));
        
        return $pdf->download('laporan-eksekutif-' . $bulan . '-' . $tahun . '.pdf');
    }

    /**
     * Laporan Per Kelas
     */
    public function perKelas(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $kelas_list = Kelas::with('waliKelas')->get();
        
        $rekap_kelas = [];
        foreach ($kelas_list as $kelas) {
            $absensi = Absensi::whereHas('jadwal', function($q) use ($kelas) {
                            $q->where('kelas_id', $kelas->id);
                        })
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->get();

            $rekap_kelas[] = [
                'kelas' => $kelas,
                'total' => $absensi->count(),
                'hadir' => $absensi->where('status', 'hadir')->count(),
                'terlambat' => $absensi->where('status', 'terlambat')->count(),
                'izin' => $absensi->whereIn('status', ['izin', 'sakit'])->count(),
                'alpha' => $absensi->where('status', 'alpha')->count(),
            ];
        }

        return view('kepala-sekolah.laporan.per-kelas', compact('rekap_kelas', 'bulan', 'tahun'));
    }
}
