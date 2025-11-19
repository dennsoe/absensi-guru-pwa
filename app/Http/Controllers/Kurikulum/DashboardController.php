<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\GuruPengganti;
use App\Models\IzinCuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the kurikulum dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Academic Statistics
        $total_jadwal = Jadwal::where('status', 'Aktif')->count();
        $jadwal_hari_ini = Jadwal::where('hari', $today->dayOfWeekIso)
            ->where('status', 'Aktif')
            ->count();

        $total_pengganti_aktif = GuruPengganti::where('status', 'approved')
            ->whereDate('tanggal', '>=', $today)
            ->count();

        $izin_pending = IzinCuti::where('status', 'pending')->count();

        // Teachers Coverage
        $guru_dengan_jadwal = Jadwal::where('status', 'Aktif')
            ->distinct('guru_id')
            ->count('guru_id');

        $total_guru_aktif = Guru::count();
        $persentase_coverage = $total_guru_aktif > 0
            ? round(($guru_dengan_jadwal / $total_guru_aktif) * 100, 1)
            : 0;

        // Today's Schedule Issues
        $jadwal_masalah = Jadwal::with(['guru', 'kelas', 'mataPelajaran'])
            ->where('hari', $today->dayOfWeekIso)
            ->where('status', 'Aktif')
            ->whereHas('guru', function($query) use ($today) {
                $query->whereHas('izinCuti', function($q) use ($today) {
                    $q->where('status', 'approved')
                        ->whereDate('tanggal_mulai', '<=', $today)
                        ->whereDate('tanggal_selesai', '>=', $today);
                });
            })
            ->get();

        // Weekly Schedule Overview
        $weekly_schedule = [];
        for ($i = 1; $i <= 7; $i++) {
            $weekly_schedule[] = [
                'hari' => $this->getDayName($i),
                'total' => Jadwal::where('hari', $i)->where('status', 'Aktif')->count()
            ];
        }

        // Recent Substitutions
        $recent_pengganti = GuruPengganti::with(['guruAsli', 'guruPengganti', 'jadwal'])
            ->whereDate('tanggal', '>=', $thisWeek)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending Approvals
        $pending_approvals = IzinCuti::with(['guru'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('kurikulum.dashboard', compact(
            'total_jadwal',
            'jadwal_hari_ini',
            'total_pengganti_aktif',
            'izin_pending',
            'persentase_coverage',
            'jadwal_masalah',
            'weekly_schedule',
            'recent_pengganti',
            'pending_approvals'
        ));
    }

    private function getDayName($day)
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        return $days[$day] ?? '';
    }
}
