<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, Guru, MataPelajaran, Kelas, Absensi};
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanAkademikController extends Controller
{
    /**
     * Landing Page Laporan Akademik
     */
    public function index()
    {
        $tahun_ajaran = '2025/2026';
        $semester = 1;

        // Total jadwal aktif
        $total_jadwal = JadwalMengajar::where('tahun_ajaran', $tahun_ajaran)
                                      ->where('semester', $semester)
                                      ->where('status', 'aktif')
                                      ->count();

        // Total jam mengajar (per minggu)
        $total_jam = JadwalMengajar::where('tahun_ajaran', $tahun_ajaran)
                                   ->where('semester', $semester)
                                   ->where('status', 'aktif')
                                   ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, jam_mulai, jam_selesai)) as total')
                                   ->first()
                                   ->total ?? 0;

        // Guru teaching this semester
        $total_guru_mengajar = JadwalMengajar::where('tahun_ajaran', $tahun_ajaran)
                                             ->where('semester', $semester)
                                             ->where('status', 'aktif')
                                             ->distinct('guru_id')
                                             ->count('guru_id');

        // Mapel aktif
        $total_mapel = JadwalMengajar::where('tahun_ajaran', $tahun_ajaran)
                                     ->where('semester', $semester)
                                     ->where('status', 'aktif')
                                     ->distinct('mapel_id')
                                     ->count('mapel_id');

        return view('kurikulum.laporan.index', compact(
            'total_jadwal',
            'total_jam',
            'total_guru_mengajar',
            'total_mapel',
            'tahun_ajaran',
            'semester'
        ));
    }

    /**
     * Laporan Per Guru
     */
    public function perGuru(Request $request)
    {
        $guru_id = $request->get('guru_id');
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');
        $semester = $request->get('semester', 1);

        $guru_list = Guru::whereHas('user', function($q) {
            $q->where('status', 'aktif');
        })->orderBy('nama')->get();

        $laporan = Guru::with(['jadwalMengajar' => function($q) use ($tahun_ajaran, $semester) {
                          $q->where('tahun_ajaran', $tahun_ajaran)
                            ->where('semester', $semester)
                            ->where('status', 'aktif')
                            ->with(['kelas', 'mataPelajaran']);
                      }])
                      ->withCount(['jadwalMengajar as total_jam_perminggu' => function($q) use ($tahun_ajaran, $semester) {
                          $q->where('tahun_ajaran', $tahun_ajaran)
                            ->where('semester', $semester)
                            ->where('status', 'aktif')
                            ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, jam_mulai, jam_selesai))');
                      }])
                      ->when($guru_id, fn($q) => $q->where('id', $guru_id))
                      ->where('status', 'aktif')
                      ->orderBy('nama')
                      ->paginate(20)
                      ->withQueryString();

        return view('kurikulum.laporan.per-guru', compact(
            'laporan',
            'guru_list',
            'guru_id',
            'tahun_ajaran',
            'semester'
        ));
    }

    /**
     * Laporan Per Mata Pelajaran
     */
    public function perMapel(Request $request)
    {
        $mapel_id = $request->get('mapel_id');
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');
        $semester = $request->get('semester', 1);

        $mapel_list = MataPelajaran::orderBy('nama_mapel')->get();

        $laporan = MataPelajaran::with(['jadwalMengajar' => function($q) use ($tahun_ajaran, $semester) {
                                    $q->where('tahun_ajaran', $tahun_ajaran)
                                      ->where('semester', $semester)
                                      ->where('status', 'aktif')
                                      ->with(['guru', 'kelas']);
                                }])
                                ->withCount(['jadwalMengajar as total_kelas' => function($q) use ($tahun_ajaran, $semester) {
                                    $q->where('tahun_ajaran', $tahun_ajaran)
                                      ->where('semester', $semester)
                                      ->where('status', 'aktif');
                                }])
                                ->when($mapel_id, fn($q) => $q->where('id', $mapel_id))
                                ->orderBy('nama_mapel')
                                ->paginate(20)
                                ->withQueryString();

        return view('kurikulum.laporan.per-mapel', compact(
            'laporan',
            'mapel_list',
            'mapel_id',
            'tahun_ajaran',
            'semester'
        ));
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $tahun_ajaran = $request->get('tahun_ajaran', '2025/2026');
        $semester = $request->get('semester', 1);

        $laporan = Guru::with(['jadwalMengajar' => function($q) use ($tahun_ajaran, $semester) {
                          $q->where('tahun_ajaran', $tahun_ajaran)
                            ->where('semester', $semester)
                            ->where('status', 'aktif')
                            ->with(['kelas', 'mataPelajaran']);
                      }])
                      ->where('status', 'aktif')
                      ->orderBy('nama')
                      ->get();

        $pdf = Pdf::loadView('kurikulum.laporan.pdf', compact('laporan', 'tahun_ajaran', 'semester'));

        return $pdf->download('laporan-akademik-' . $tahun_ajaran . '.pdf');
    }
}
