<?php

namespace App\Services;

use App\Models\{Guru, Absensi, Kelas, JadwalMengajar, IzinCuti};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class LaporanService
{
    /**
     * Generate laporan per guru
     */
    public function generateLaporanPerGuru($guruId, $bulan, $tahun, $format = 'pdf')
    {
        $guru = Guru::findOrFail($guruId);

        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        // Get absensi data
        $absensiData = Absensi::where('guru_id', $guruId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->get();

        // Calculate statistics
        $statistics = [
            'total_jadwal' => JadwalMengajar::where('guru_id', $guruId)
                
                ->count(),
            'total_absensi' => $absensiData->count(),
            'hadir' => $absensiData->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensiData->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensiData->where('status_kehadiran', 'izin')->count(),
            'sakit' => $absensiData->where('status_kehadiran', 'sakit')->count(),
            'cuti' => $absensiData->where('status_kehadiran', 'cuti')->count(),
            'alpha' => $absensiData->where('status_kehadiran', 'alpha')->count(),
        ];

        $statistics['persentase_kehadiran'] = $statistics['total_absensi'] > 0
            ? round((($statistics['hadir'] + $statistics['terlambat']) / $statistics['total_absensi']) * 100, 2)
            : 0;

        // Get izin/cuti data
        $izinCutiData = IzinCuti::where('guru_id', $guruId)
            ->whereBetween('tanggal_mulai', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        $data = [
            'guru' => $guru,
            'periode' => [
                'bulan' => $startDate->locale('id')->monthName,
                'tahun' => $tahun,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'absensi' => $absensiData,
            'statistics' => $statistics,
            'izin_cuti' => $izinCutiData,
            'generated_at' => Carbon::now(),
        ];

        if ($format === 'excel') {
            return $this->exportToExcel($data, 'laporan_per_guru');
        }

        return $this->generatePDF($data, 'pdf.laporan-per-guru');
    }

    /**
     * Generate laporan per kelas
     */
    public function generateLaporanPerKelas($kelasId, $bulan, $tahun, $format = 'pdf')
    {
        $kelas = Kelas::with('ketuaKelas')->findOrFail($kelasId);

        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        // Get jadwal for this class
        $jadwalList = JadwalMengajar::where('kelas_id', $kelasId)
            
            ->with('guru', 'mataPelajaran')
            ->get();

        // Get absensi for each jadwal
        $absensiPerGuru = [];
        foreach ($jadwalList as $jadwal) {
            $absensi = Absensi::where('jadwal_id', $jadwal->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get();

            $absensiPerGuru[$jadwal->guru_id] = [
                'guru' => $jadwal->guru,
                'mata_pelajaran' => $jadwal->mataPelajaran->nama_mapel,
                'total' => $absensi->count(),
                'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
                'izin' => $absensi->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
                'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
            ];

            $absensiPerGuru[$jadwal->guru_id]['persentase'] = $absensiPerGuru[$jadwal->guru_id]['total'] > 0
                ? round((($absensiPerGuru[$jadwal->guru_id]['hadir'] + $absensiPerGuru[$jadwal->guru_id]['terlambat']) / $absensiPerGuru[$jadwal->guru_id]['total']) * 100, 2)
                : 0;
        }

        $data = [
            'kelas' => $kelas,
            'periode' => [
                'bulan' => $startDate->locale('id')->monthName,
                'tahun' => $tahun,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'jadwal_list' => $jadwalList,
            'absensi_per_guru' => $absensiPerGuru,
            'generated_at' => Carbon::now(),
        ];

        if ($format === 'excel') {
            return $this->exportToExcel($data, 'laporan_per_kelas');
        }

        return $this->generatePDF($data, 'pdf.laporan-per-kelas');
    }

    /**
     * Generate laporan rekap bulanan
     */
    public function generateLaporanRekapBulanan($bulan, $tahun, $format = 'pdf')
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        // Get all active guru
        $guruList = Guru::all();

        $rekapData = [];
        foreach ($guruList as $guru) {
            $absensi = Absensi::where('guru_id', $guru->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get();

            $rekapData[] = [
                'guru' => $guru,
                'total_jadwal' => JadwalMengajar::where('guru_id', $guru->id)
                    
                    ->count(),
                'total' => $absensi->count(),
                'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
                'izin' => $absensi->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
                'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
            ];
        }

        // Calculate totals
        $totalStatistics = [
            'total_guru' => count($rekapData),
            'total_absensi' => array_sum(array_column($rekapData, 'total')),
            'total_hadir' => array_sum(array_column($rekapData, 'hadir')),
            'total_terlambat' => array_sum(array_column($rekapData, 'terlambat')),
            'total_izin' => array_sum(array_column($rekapData, 'izin')),
            'total_alpha' => array_sum(array_column($rekapData, 'alpha')),
        ];

        $data = [
            'periode' => [
                'bulan' => $startDate->locale('id')->monthName,
                'tahun' => $tahun,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'rekap_data' => $rekapData,
            'total_statistics' => $totalStatistics,
            'generated_at' => Carbon::now(),
        ];

        if ($format === 'excel') {
            return $this->exportToExcel($data, 'laporan_rekap_bulanan');
        }

        return $this->generatePDF($data, 'pdf.laporan-rekap-bulanan');
    }

    /**
     * Generate laporan keterlambatan
     */
    public function generateLaporanKeterlambatan($bulan, $tahun, $format = 'pdf')
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $terlambatData = Absensi::where('status_kehadiran', 'terlambat')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with(['guru', 'jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
            ->orderBy('tanggal')
            ->get()
            ->groupBy('guru_id');

        $rekapTerlambat = [];
        foreach ($terlambatData as $guruId => $absensiList) {
            $guru = $absensiList->first()->guru;

            $rekapTerlambat[] = [
                'guru' => $guru,
                'total_terlambat' => $absensiList->count(),
                'detail' => $absensiList->map(function($item) {
                    return [
                        'tanggal' => $item->tanggal,
                        'jam_masuk' => $item->jam_masuk,
                        'kelas' => $item->jadwalMengajar->kelas->nama_kelas ?? '-',
                        'mata_pelajaran' => $item->jadwalMengajar->mataPelajaran->nama_mapel ?? '-',
                        'keterangan' => $item->keterangan,
                    ];
                }),
            ];
        }

        // Sort by total terlambat descending
        usort($rekapTerlambat, function($a, $b) {
            return $b['total_terlambat'] - $a['total_terlambat'];
        });

        $data = [
            'periode' => [
                'bulan' => $startDate->locale('id')->monthName,
                'tahun' => $tahun,
            ],
            'rekap_terlambat' => $rekapTerlambat,
            'total_terlambat' => array_sum(array_column($rekapTerlambat, 'total_terlambat')),
            'generated_at' => Carbon::now(),
        ];

        if ($format === 'excel') {
            return $this->exportToExcel($data, 'laporan_keterlambatan');
        }

        return $this->generatePDF($data, 'pdf.laporan-keterlambatan');
    }

    /**
     * Generate PDF
     */
    private function generatePDF($data, $view)
    {
        $pdf = PDF::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Export to Excel using maatwebsite/excel
     */
    private function exportToExcel($data, $type)
    {
        $filename = $type . '_' . date('Y-m-d_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanExport($data, $type),
            $filename
        );
    }
}
