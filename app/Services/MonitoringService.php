<?php

namespace App\Services;

use App\Models\{Guru, Absensi, Kelas, JadwalMengajar, IzinCuti, GuruPiket};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitoringService
{
    /**
     * Get real-time monitoring dashboard data
     */
    public function getDashboardData($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $hari = ucfirst($date->locale('id')->dayName);

        return [
            'tanggal' => $date,
            'hari' => $hari,
            'guru_aktif' => $this->getGuruAktifCount(),
            'jadwal_hari_ini' => $this->getJadwalHariIni($hari),
            'absensi_hari_ini' => $this->getAbsensiHariIni($date),
            'guru_piket' => $this->getGuruPiketHariIni($hari),
            'izin_cuti_aktif' => $this->getIzinCutiAktif($date),
            'alerts' => $this->getAlerts($date, $hari),
        ];
    }

    /**
     * Get count of active guru
     */
    public function getGuruAktifCount()
    {
        return Guru::count();
    }

    /**
     * Get jadwal for today
     */
    public function getJadwalHariIni($hari)
    {
        $jadwalList = JadwalMengajar::where('hari', $hari)
            
            ->with(['guru', 'kelas', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        return [
            'total' => $jadwalList->count(),
            'list' => $jadwalList,
        ];
    }

    /**
     * Get absensi for today
     */
    public function getAbsensiHariIni($date)
    {
        $absensiList = Absensi::whereDate('tanggal', $date)
            ->with(['guru', 'jadwalMengajar'])
            ->get();

        return [
            'total' => $absensiList->count(),
            'hadir' => $absensiList->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensiList->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensiList->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
            'alpha' => $absensiList->where('status_kehadiran', 'alpha')->count(),
            'belum_absen' => $this->getBelumAbsenCount($date),
            'list' => $absensiList,
        ];
    }

    /**
     * Get guru piket for today
     */
    public function getGuruPiketHariIni($hari)
    {
        $guruPiketList = GuruPiket::where('hari', $hari)
            
            ->with('guru')
            ->get();

        return [
            'total' => $guruPiketList->count(),
            'list' => $guruPiketList,
        ];
    }

    /**
     * Get active izin/cuti today
     */
    public function getIzinCutiAktif($date)
    {
        $izinCutiList = IzinCuti::where('status', 'approved')
            ->where('tanggal_mulai', '<=', $date)
            ->where('tanggal_selesai', '>=', $date)
            ->with('guru')
            ->get();

        return [
            'total' => $izinCutiList->count(),
            'list' => $izinCutiList,
        ];
    }

    /**
     * Get alerts for monitoring
     */
    public function getAlerts($date, $hari)
    {
        $alerts = [];
        $currentTime = Carbon::now();

        // Alert: Jadwal yang sedang berlangsung tapi belum absen
        $jadwalBerlangsung = JadwalMengajar::where('hari', $hari)
            
            ->whereTime('jam_mulai', '<=', $currentTime)
            ->whereTime('jam_selesai', '>=', $currentTime)
            ->with('guru')
            ->get();

        foreach ($jadwalBerlangsung as $jadwal) {
            $hasAbsensi = Absensi::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', $date)
                ->exists();

            if (!$hasAbsensi) {
                $alerts[] = [
                    'type' => 'danger',
                    'priority' => 'high',
                    'title' => 'Jadwal Berlangsung Belum Absen',
                    'message' => "Guru {$jadwal->guru->nama} belum absen untuk jadwal {$jadwal->mataPelajaran->nama_mapel} di kelas {$jadwal->kelas->nama_kelas}",
                    'time' => $currentTime->diffForHumans(),
                ];
            }
        }

        // Alert: Guru dengan banyak alpha
        $guruAlphaCount = Absensi::whereMonth('tanggal', $currentTime->month)
            ->whereYear('tanggal', $currentTime->year)
            ->where('status_kehadiran', 'alpha')
            ->select('guru_id', DB::raw('COUNT(*) as total_alpha'))
            ->groupBy('guru_id')
            ->having('total_alpha', '>=', 3)
            ->with('guru')
            ->get();

        foreach ($guruAlphaCount as $record) {
            $alerts[] = [
                'type' => 'warning',
                'priority' => 'medium',
                'title' => 'Alpha Berlebihan',
                'message' => "Guru {$record->guru->nama} memiliki {$record->total_alpha} kali alpha bulan ini",
                'time' => 'Bulan ini',
            ];
        }

        // Alert: Izin/Cuti pending approval
        $pendingApproval = IzinCuti::where('status', 'pending')->count();
        if ($pendingApproval > 0) {
            $alerts[] = [
                'type' => 'info',
                'priority' => 'low',
                'title' => 'Izin/Cuti Menunggu Approval',
                'message' => "Ada {$pendingApproval} permohonan izin/cuti yang menunggu persetujuan",
                'time' => 'Sekarang',
            ];
        }

        // Sort by priority
        usort($alerts, function($a, $b) {
            $priority = ['high' => 1, 'medium' => 2, 'low' => 3];
            return ($priority[$a['priority']] ?? 99) - ($priority[$b['priority']] ?? 99);
        });

        return $alerts;
    }

    /**
     * Get count of guru who haven't checked in
     */
    private function getBelumAbsenCount($date)
    {
        $hari = ucfirst(Carbon::parse($date)->locale('id')->dayName);

        $totalJadwal = JadwalMengajar::where('hari', $hari)
            
            ->count();

        $sudahAbsen = Absensi::whereDate('tanggal', $date)->count();

        return max(0, $totalJadwal - $sudahAbsen);
    }

    /**
     * Get live status of all guru today
     */
    public function getLiveGuruStatus($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $hari = ucfirst($date->locale('id')->dayName);
        $currentTime = Carbon::now();

        $guruList = Guru::with(['jadwalMengajar' => function($q) use ($hari) {
                $q->where('hari', $hari);
            }])
            ->get();

        $guruStatus = [];

        foreach ($guruList as $guru) {
            $jadwalHariIni = $guru->jadwalMengajar;

            if ($jadwalHariIni->isEmpty()) {
                $guruStatus[] = [
                    'guru' => $guru,
                    'status' => 'no_schedule',
                    'label' => 'Tidak Ada Jadwal',
                    'color' => 'secondary',
                ];
                continue;
            }

            // Check if on leave
            $onLeave = IzinCuti::where('guru_id', $guru->id)
                ->where('status', 'approved')
                ->where('tanggal_mulai', '<=', $date)
                ->where('tanggal_selesai', '>=', $date)
                ->exists();

            if ($onLeave) {
                $guruStatus[] = [
                    'guru' => $guru,
                    'status' => 'on_leave',
                    'label' => 'Sedang Izin/Cuti',
                    'color' => 'info',
                ];
                continue;
            }

            // Check absensi
            $absensiToday = Absensi::where('guru_id', $guru->id)
                ->whereDate('tanggal', $date)
                ->get();

            if ($absensiToday->isEmpty()) {
                $guruStatus[] = [
                    'guru' => $guru,
                    'status' => 'not_checked_in',
                    'label' => 'Belum Absen',
                    'color' => 'danger',
                    'total_jadwal' => $jadwalHariIni->count(),
                ];
            } else {
                $statusKehadiran = $absensiToday->first()->status_kehadiran;

                $statusMap = [
                    'hadir' => ['label' => 'Hadir', 'color' => 'success'],
                    'terlambat' => ['label' => 'Terlambat', 'color' => 'warning'],
                    'izin' => ['label' => 'Izin', 'color' => 'info'],
                    'sakit' => ['label' => 'Sakit', 'color' => 'info'],
                    'alpha' => ['label' => 'Alpha', 'color' => 'danger'],
                ];

                $guruStatus[] = [
                    'guru' => $guru,
                    'status' => $statusKehadiran,
                    'label' => $statusMap[$statusKehadiran]['label'] ?? 'Unknown',
                    'color' => $statusMap[$statusKehadiran]['color'] ?? 'secondary',
                    'total_absensi' => $absensiToday->count(),
                    'total_jadwal' => $jadwalHariIni->count(),
                ];
            }
        }

        return $guruStatus;
    }

    /**
     * Get statistics for a specific period
     */
    public function getPeriodStatistics($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $absensiData = Absensi::whereBetween('tanggal', [$startDate, $endDate])->get();

        return [
            'periode' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'total_absensi' => $absensiData->count(),
            'hadir' => $absensiData->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensiData->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensiData->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
            'alpha' => $absensiData->where('status_kehadiran', 'alpha')->count(),
            'persentase_kehadiran' => $absensiData->count() > 0
                ? round((($absensiData->where('status_kehadiran', 'hadir')->count() +
                         $absensiData->where('status_kehadiran', 'terlambat')->count()) /
                         $absensiData->count()) * 100, 2)
                : 0,
        ];
    }
}
