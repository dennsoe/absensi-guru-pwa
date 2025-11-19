<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\IzinCuti;
use App\Models\Jadwal;
use App\Services\{MonitoringService, StatistikService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $monitoringService;
    protected $statistikService;

    public function __construct(MonitoringService $monitoringService, StatistikService $statistikService)
    {
        $this->monitoringService = $monitoringService;
        $this->statistikService = $statistikService;
    }
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::today()->subDays(6);

        // Get dashboard data from MonitoringService
        $dashboardData = $this->monitoringService->getDashboardData($today);

        // Total Statistics
        $total_guru = $dashboardData['guru_aktif'];
        $guru_aktif = $dashboardData['guru_aktif'];
        $total_kelas = DB::table('kelas')->count();
        $total_jadwal = $dashboardData['jadwal_hari_ini']['total'];

        // Today's Statistics from monitoring
        $absensi_today = $dashboardData['absensi_hari_ini'];
        $guru_hadir_hari_ini = $absensi_today['hadir'] + $absensi_today['terlambat'];
        $guru_terlambat_hari_ini = $absensi_today['terlambat'];
        $guru_izin_hari_ini = $absensi_today['izin'];
        $alpha_hari_ini = $absensi_today['alpha'];

        // Calculate percentage
        $persentase_hadir = $total_guru > 0
            ? round(($guru_hadir_hari_ini / $total_guru) * 100, 1)
            : 0;

        // Alerts from monitoring
        $alerts = $dashboardData['alerts'];
        $izin_pending = count(array_filter($alerts, fn($a) => $a['title'] === 'Izin/Cuti Menunggu Approval'));

        // Latest Absensi (last 10)
        $latest_absensi = Absensi::with(['guru'])
            ->whereDate('tanggal', '>=', $sevenDaysAgo)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Pending Izin List (last 5)
        $pending_izin = IzinCuti::with(['guru'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data - Last 7 Days using StatistikService
        $chart_labels = [];
        $chart_hadir = [];
        $chart_izin = [];
        $chart_alpha = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chart_labels[] = $date->isoFormat('ddd');
            $stats = $this->monitoringService->getPeriodStatistics($date, $date);

            $chart_hadir[] = $stats['hadir'] + $stats['terlambat'];
            $chart_izin[] = $stats['izin'];
            $chart_alpha[] = $stats['alpha'];
        }

        return view('admin.dashboard', compact(
            'total_guru',
            'guru_aktif',
            'total_kelas',
            'total_jadwal',
            'guru_hadir_hari_ini',
            'guru_terlambat_hari_ini',
            'guru_izin_hari_ini',
            'alpha_hari_ini',
            'persentase_hadir',
            'izin_pending',
            'latest_absensi',
            'pending_izin',
            'chart_labels',
            'chart_hadir',
            'chart_izin',
            'chart_alpha',
            'alerts'
        ));
    }

    /**
     * Get real-time statistics (for AJAX refresh)
     */
    public function getRealtimeStats()
    {
        $today = Carbon::today();
        $dashboardData = $this->monitoringService->getDashboardData($today);

        $absensi = $dashboardData['absensi_hari_ini'];

        $stats = [
            'guru_hadir' => $absensi['hadir'] + $absensi['terlambat'],
            'guru_terlambat' => $absensi['terlambat'],
            'guru_izin' => $absensi['izin'],
            'alpha' => $absensi['alpha'],
            'izin_pending' => IzinCuti::where('status', 'pending')->count(),
            'alerts_count' => count($dashboardData['alerts']),
        ];

        return response()->json($stats);
    }

    /**
     * Get live guru status (AJAX)
     */
    public function getLiveGuruStatus()
    {
        $liveStatus = $this->monitoringService->getLiveGuruStatus();
        return response()->json($liveStatus);
    }
}
