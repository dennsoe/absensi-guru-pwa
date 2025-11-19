<?php

namespace App\Services;

use App\Models\{Guru, Absensi, Kelas, JadwalMengajar, IzinCuti};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistikService
{
    /**
     * Get comprehensive guru statistics
     */
    public function getGuruStatistics($guruId, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;
        $query = Absensi::where('guru_id', $guruId)->whereYear('tanggal', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        $absensiData = $query->get();
        $izinCutiData = IzinCuti::where('guru_id', $guruId)
            ->whereYear('tanggal_mulai', $tahun)
            ->where('status', 'approved');

        if ($bulan) {
            $izinCutiData->whereMonth('tanggal_mulai', $bulan);
        }

        $izinCutiData = $izinCutiData->get();

        return [
            'periode' => $bulan
                ? Carbon::create($tahun, $bulan, 1)->format('F Y')
                : $tahun,
            'total_hari' => $absensiData->count(),
            'hadir' => $absensiData->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensiData->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensiData->where('status_kehadiran', 'izin')->count(),
            'sakit' => $absensiData->where('status_kehadiran', 'sakit')->count(),
            'cuti' => $absensiData->where('status_kehadiran', 'cuti')->count(),
            'alpha' => $absensiData->where('status_kehadiran', 'alpha')->count(),
            'persentase_kehadiran' => $this->calculateKehadiranPercentage($absensiData),
            'rata_rata_keterlambatan' => $this->calculateAverageLateness($absensiData),
            'total_izin_cuti' => $izinCutiData->count(),
            'trend' => $this->calculateTrend($guruId, $tahun, $bulan),
        ];
    }

    /**
     * Calculate attendance percentage
     */
    private function calculateKehadiranPercentage($absensiData)
    {
        $total = $absensiData->count();
        if ($total === 0) return 0;

        $hadir = $absensiData->whereIn('status_kehadiran', ['hadir', 'terlambat'])->count();
        return round(($hadir / $total) * 100, 2);
    }

    /**
     * Calculate average lateness in minutes
     */
    private function calculateAverageLateness($absensiData)
    {
        $terlambatRecords = $absensiData->where('status_kehadiran', 'terlambat');

        if ($terlambatRecords->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($terlambatRecords as $record) {
            if ($record->waktu_terlambat) {
                $totalMinutes += $record->waktu_terlambat;
                $count++;
            }
        }

        return $count > 0 ? round($totalMinutes / $count, 0) : 0;
    }

    /**
     * Calculate attendance trend (compare with previous period)
     */
    private function calculateTrend($guruId, $tahun, $bulan = null)
    {
        if ($bulan) {
            $currentPeriod = Absensi::where('guru_id', $guruId)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->get();

            $previousMonth = $bulan > 1 ? $bulan - 1 : 12;
            $previousYear = $bulan > 1 ? $tahun : $tahun - 1;

            $previousPeriod = Absensi::where('guru_id', $guruId)
                ->whereYear('tanggal', $previousYear)
                ->whereMonth('tanggal', $previousMonth)
                ->get();
        } else {
            $currentPeriod = Absensi::where('guru_id', $guruId)
                ->whereYear('tanggal', $tahun)
                ->get();

            $previousPeriod = Absensi::where('guru_id', $guruId)
                ->whereYear('tanggal', $tahun - 1)
                ->get();
        }

        $currentPercentage = $this->calculateKehadiranPercentage($currentPeriod);
        $previousPercentage = $this->calculateKehadiranPercentage($previousPeriod);

        $change = $currentPercentage - $previousPercentage;

        return [
            'current' => $currentPercentage,
            'previous' => $previousPercentage,
            'change' => round($change, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * Get top performers based on attendance
     */
    public function getTopPerformers($limit = 10, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;

        $guruList = Guru::all();
        $rankings = [];

        foreach ($guruList as $guru) {
            $stats = $this->getGuruStatistics($guru->id, $tahun, $bulan);
            $rankings[] = [
                'guru' => $guru,
                'persentase_kehadiran' => $stats['persentase_kehadiran'],
                'total_hadir' => $stats['hadir'],
                'total_terlambat' => $stats['terlambat'],
                'total_alpha' => $stats['alpha'],
            ];
        }

        usort($rankings, function($a, $b) {
            if ($a['persentase_kehadiran'] === $b['persentase_kehadiran']) {
                return $b['total_hadir'] - $a['total_hadir'];
            }
            return $b['persentase_kehadiran'] <=> $a['persentase_kehadiran'];
        });

        return array_slice($rankings, 0, $limit);
    }

    /**
     * Get worst performers (attendance concerns)
     */
    public function getWorstPerformers($limit = 10, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;

        $guruList = Guru::all();
        $rankings = [];

        foreach ($guruList as $guru) {
            $stats = $this->getGuruStatistics($guru->id, $tahun, $bulan);

            // Only include if they have attendance records
            if ($stats['total_hari'] > 0) {
                $rankings[] = [
                    'guru' => $guru,
                    'persentase_kehadiran' => $stats['persentase_kehadiran'],
                    'total_alpha' => $stats['alpha'],
                    'total_terlambat' => $stats['terlambat'],
                    'total_hari' => $stats['total_hari'],
                ];
            }
        }

        usort($rankings, function($a, $b) {
            if ($a['persentase_kehadiran'] === $b['persentase_kehadiran']) {
                return $b['total_alpha'] - $a['total_alpha'];
            }
            return $a['persentase_kehadiran'] <=> $b['persentase_kehadiran'];
        });

        return array_slice($rankings, 0, $limit);
    }

    /**
     * Get kelas statistics
     */
    public function getKelasStatistics($kelasId, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;
        $kelas = Kelas::findOrFail($kelasId);

        $jadwalList = JadwalMengajar::where('kelas_id', $kelasId)
            
            ->with('guru')
            ->get();

        $guruStats = [];

        foreach ($jadwalList as $jadwal) {
            $query = Absensi::where('guru_id', $jadwal->guru_id)
                ->where('jadwal_id', $jadwal->id)
                ->whereYear('tanggal', $tahun);

            if ($bulan) {
                $query->whereMonth('tanggal', $bulan);
            }

            $absensiData = $query->get();

            $guruStats[] = [
                'guru' => $jadwal->guru,
                'mata_pelajaran' => $jadwal->mataPelajaran->nama_mapel,
                'total_pertemuan' => $absensiData->count(),
                'hadir' => $absensiData->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $absensiData->where('status_kehadiran', 'terlambat')->count(),
                'alpha' => $absensiData->where('status_kehadiran', 'alpha')->count(),
                'persentase_kehadiran' => $this->calculateKehadiranPercentage($absensiData),
            ];
        }

        return [
            'kelas' => $kelas,
            'periode' => $bulan
                ? Carbon::create($tahun, $bulan, 1)->format('F Y')
                : $tahun,
            'guru_statistics' => $guruStats,
            'total_guru' => count($guruStats),
        ];
    }

    /**
     * Get overall system statistics
     */
    public function getOverallStatistics($tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;

        $query = Absensi::whereYear('tanggal', $tahun);
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        $allAbsensi = $query->get();

        $guruAktif = Guru::count();
        $kelasAktif = Kelas::where('status', 'aktif')->count();

        return [
            'periode' => $bulan
                ? Carbon::create($tahun, $bulan, 1)->format('F Y')
                : $tahun,
            'total_guru_aktif' => $guruAktif,
            'total_kelas_aktif' => $kelasAktif,
            'total_absensi' => $allAbsensi->count(),
            'total_hadir' => $allAbsensi->where('status_kehadiran', 'hadir')->count(),
            'total_terlambat' => $allAbsensi->where('status_kehadiran', 'terlambat')->count(),
            'total_izin' => $allAbsensi->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
            'total_alpha' => $allAbsensi->where('status_kehadiran', 'alpha')->count(),
            'persentase_kehadiran' => $this->calculateKehadiranPercentage($allAbsensi),
            'rata_rata_guru_per_hari' => $this->calculateAverageDailyAttendance($tahun, $bulan),
        ];
    }

    /**
     * Calculate average daily attendance
     */
    private function calculateAverageDailyAttendance($tahun, $bulan = null)
    {
        $query = DB::table('absensi')
            ->select(DB::raw('DATE(tanggal) as date'), DB::raw('COUNT(*) as count'))
            ->whereYear('tanggal', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }

        $dailyCounts = $query->groupBy('date')->get();

        if ($dailyCounts->isEmpty()) {
            return 0;
        }

        $totalCount = $dailyCounts->sum('count');
        return round($totalCount / $dailyCounts->count(), 1);
    }

    /**
     * Get monthly comparison data for charts
     */
    public function getMonthlyComparison($tahun = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $absensi = Absensi::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $month)
                ->get();

            $monthlyData[] = [
                'bulan' => Carbon::create($tahun, $month, 1)->format('M'),
                'total' => $absensi->count(),
                'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
                'alpha' => $absensi->where('status_kehadiran', 'alpha')->count(),
            ];
        }

        return $monthlyData;
    }
}
