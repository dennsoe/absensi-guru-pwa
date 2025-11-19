<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\IzinCuti;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the kepala sekolah dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Executive Summary
        $total_guru = Guru::count();
        $total_kelas = DB::table('kelas')->count();
        $total_mata_pelajaran = DB::table('mata_pelajaran')->count();

        // Today's Attendance
        $hadir_hari_ini = Absensi::whereDate('tanggal', $today)
            ->whereIn('status_kehadiran', ['Hadir', 'Terlambat'])
            ->distinct('guru_id')
            ->count('guru_id');

        $terlambat_hari_ini = Absensi::whereDate('tanggal', $today)
            ->where('status_kehadiran', 'Terlambat')
            ->distinct('guru_id')
            ->count('guru_id');

        $izin_hari_ini = Absensi::whereDate('tanggal', $today)
            ->whereIn('status_kehadiran', ['Izin', 'Sakit', 'Cuti', 'Dinas Luar'])
            ->distinct('guru_id')
            ->count('guru_id');

        $alpha_hari_ini = Absensi::whereDate('tanggal', $today)
            ->where('status_kehadiran', 'Alpha')
            ->distinct('guru_id')
            ->count('guru_id');

        // Attendance Percentage
        $persentase_kehadiran = $total_guru > 0
            ? round(($hadir_hari_ini / $total_guru) * 100, 1)
            : 0;

        // Pending Approvals
        $izin_pending = IzinCuti::where('status', 'pending')->count();

        // Monthly Trend (Last 30 days)
        $trend_data = [];
        $trend_labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trend_labels[] = $date->format('d/m');

            $hadir = Absensi::whereDate('tanggal', $date)
                ->whereIn('status_kehadiran', ['Hadir', 'Terlambat'])
                ->distinct('guru_id')
                ->count('guru_id');

            $trend_data[] = $hadir;
        }

        // Top Performing Teachers (Most present this month)
        $top_teachers = Guru::select('guru.*', DB::raw('COUNT(DISTINCT absensi.id) as total_hadir'))
            ->join('absensi', 'guru.id', '=', 'absensi.guru_id')
            ->whereMonth('absensi.tanggal', $thisMonth)
            ->whereYear('absensi.tanggal', $thisYear)
            ->whereIn('absensi.status_kehadiran', ['Hadir', 'Terlambat'])
            ->groupBy('guru.id')
            ->orderBy('total_hadir', 'desc')
            ->limit(5)
            ->get();

        // Teachers Needing Attention (Most alpha this month)
        $attention_teachers = Guru::select('guru.*', DB::raw('COUNT(DISTINCT absensi.id) as total_alpha'))
            ->join('absensi', 'guru.id', '=', 'absensi.guru_id')
            ->whereMonth('absensi.tanggal', $thisMonth)
            ->whereYear('absensi.tanggal', $thisYear)
            ->where('absensi.status_kehadiran', 'Alpha')
            ->groupBy('guru.id')
            ->orderBy('total_alpha', 'desc')
            ->limit(5)
            ->get();

        // Recent Activities
        $recent_activities = Absensi::with(['guru'])
            ->whereDate('tanggal', '>=', Carbon::today()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('kepala-sekolah.dashboard', compact(
            'total_guru',
            'total_kelas',
            'total_mata_pelajaran',
            'hadir_hari_ini',
            'terlambat_hari_ini',
            'izin_hari_ini',
            'alpha_hari_ini',
            'persentase_kehadiran',
            'izin_pending',
            'trend_data',
            'trend_labels',
            'top_teachers',
            'attention_teachers',
            'recent_activities'
        ));
    }
}
