<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, Laporan};
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class LaporanController extends Controller
{
    /**
     * Halaman Laporan Rekap Absensi
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $guruId = $request->get('guru_id');

        $query = Absensi::query()
                        ->select(
                            'guru_id',
                            DB::raw('COUNT(*) as total_absensi'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "hadir" THEN 1 ELSE 0 END) as hadir'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "izin" THEN 1 ELSE 0 END) as izin'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "sakit" THEN 1 ELSE 0 END) as sakit'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "alpha" THEN 1 ELSE 0 END) as alpha'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "dinas_luar" THEN 1 ELSE 0 END) as dinas_luar'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "terlambat" THEN 1 ELSE 0 END) as terlambat')
                        )
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->groupBy('guru_id')
                        ->with('guru');

        if ($guruId) {
            $query->where('guru_id', $guruId);
        }

        $rekap = $query->get();
        $guru = Guru::whereHas('user', function($q) {
            $q;
        })->get();

        return view('laporan.index', compact('rekap', 'bulan', 'tahun', 'guru', 'guruId'));
    }

    /**
     * Export Laporan ke PDF
     */
    public function exportPdf(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $guruId = $request->get('guru_id');

        $query = Absensi::select(
                            'guru_id',
                            DB::raw('COUNT(*) as total_absensi'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "hadir" THEN 1 ELSE 0 END) as hadir'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "izin" THEN 1 ELSE 0 END) as izin'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "sakit" THEN 1 ELSE 0 END) as sakit'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "alpha" THEN 1 ELSE 0 END) as alpha'),
                            DB::raw('SUM(CASE WHEN status_kehadiran = "terlambat" THEN 1 ELSE 0 END) as terlambat')
                        )
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->groupBy('guru_id')
                        ->with('guru');

        if ($guruId) {
            $query->where('guru_id', $guruId);
        }

        $rekap = $query->get();
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $data = [
            'rekap' => $rekap,
            'bulan' => $namaBulan[$bulan],
            'tahun' => $tahun,
            'tanggal_cetak' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('laporan.pdf', $data)
                  ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-absensi-' . $bulan . '-' . $tahun . '.pdf');
    }

    /**
     * Export Laporan ke Excel
     */
    public function exportExcel(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $guruId = $request->get('guru_id');

        return Excel::download(
            new AbsensiExport($bulan, $tahun, $guruId),
            'laporan-absensi-' . $bulan . '-' . $tahun . '.xlsx'
        );
    }

    /**
     * Detail Absensi Per Guru
     */
    public function detailGuru(Request $request, Guru $guru)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $absensi = Absensi::where('guru_id', $guru->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                          ->orderBy('tanggal')
                          ->get();

        return view('laporan.detail-guru', compact('guru', 'absensi', 'bulan', 'tahun'));
    }

    /**
     * Simpan Laporan (untuk arsip)
     */
    public function simpanLaporan(Request $request)
    {
        $validated = $request->validate([
            'jenis_laporan' => 'required|in:harian,mingguan,bulanan,tahunan',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
        ]);

        $validated['dibuat_oleh'] = auth()->id();
        $validated['status'] = 'draft';

        $laporan = Laporan::create($validated);

        return redirect()->route('laporan.index')
                        ->with('success', 'Laporan berhasil disimpan sebagai draft.');
    }
}
