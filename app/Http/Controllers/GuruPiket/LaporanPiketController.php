<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar};
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPiketController extends Controller
{
    /**
     * Laporan Piket Harian
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', today()->toDateString());
        $hari = Carbon::parse($tanggal)->locale('id')->dayName;

        // Jadwal hari tersebut
        $jadwal = JadwalMengajar::where('hari', $hari)
                                ->where('status', 'aktif')
                                ->with(['guru', 'kelas', 'mataPelajaran'])
                                ->orderBy('jam_mulai')
                                ->get();

        // Absensi
        $absensi = Absensi::whereDate('tanggal', $tanggal)
                          ->with(['guru', 'jadwal.kelas', 'jadwal.mataPelajaran'])
                          ->get();

        // Statistik
        $stats = [
            'total_jadwal' => $jadwal->count(),
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
            'izin' => $absensi->whereIn('status', ['izin', 'sakit'])->count(),
            'alpha' => $absensi->where('status', 'alpha')->count(),
            'belum_absen' => $jadwal->count() - $absensi->count(),
        ];

        // Catatan khusus
        $catatan_khusus = [
            'guru_terlambat' => $absensi->where('status', 'terlambat'),
            'guru_belum_absen' => $jadwal->filter(function($j) use ($absensi) {
                return !$absensi->pluck('guru_id')->contains($j->guru_id);
            })->unique('guru_id'),
        ];

        return view('guru-piket.laporan.index', compact(
            'tanggal',
            'hari',
            'jadwal',
            'absensi',
            'stats',
            'catatan_khusus'
        ));
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $tanggal = $request->get('tanggal', today()->toDateString());
        $hari = Carbon::parse($tanggal)->locale('id')->dayName;

        $jadwal = JadwalMengajar::where('hari', $hari)
                                ->where('status', 'aktif')
                                ->with(['guru', 'kelas', 'mataPelajaran'])
                                ->orderBy('jam_mulai')
                                ->get();

        $absensi = Absensi::whereDate('tanggal', $tanggal)
                          ->with(['guru', 'jadwal.kelas', 'jadwal.mataPelajaran'])
                          ->get();

        $stats = [
            'total_jadwal' => $jadwal->count(),
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
            'izin' => $absensi->whereIn('status', ['izin', 'sakit'])->count(),
            'alpha' => $absensi->where('status', 'alpha')->count(),
        ];

        $pdf = Pdf::loadView('guru-piket.laporan.pdf', compact(
            'tanggal',
            'hari',
            'jadwal',
            'absensi',
            'stats'
        ));

        return $pdf->download('laporan-piket-' . $tanggal . '.pdf');
    }

    /**
     * Laporan Mingguan
     */
    public function mingguan(Request $request)
    {
        $minggu = $request->get('minggu', now()->weekOfYear);
        $tahun = $request->get('tahun', now()->year);

        $start = Carbon::now()->setISODate($tahun, $minggu)->startOfWeek();
        $end = Carbon::now()->setISODate($tahun, $minggu)->endOfWeek();

        $absensi = Absensi::whereBetween('tanggal', [$start, $end])
                          ->with(['guru', 'jadwal'])
                          ->get()
                          ->groupBy('guru_id');

        $rekap = [];
        foreach ($absensi as $guruId => $items) {
            $rekap[] = [
                'guru' => $items->first()->guru,
                'total' => $items->count(),
                'hadir' => $items->where('status', 'hadir')->count(),
                'terlambat' => $items->where('status', 'terlambat')->count(),
                'izin' => $items->whereIn('status', ['izin', 'sakit'])->count(),
                'alpha' => $items->where('status', 'alpha')->count(),
            ];
        }

        return view('guru-piket.laporan.mingguan', compact('rekap', 'start', 'end', 'minggu', 'tahun'));
    }
}
