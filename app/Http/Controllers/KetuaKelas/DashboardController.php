<?php

namespace App\Http\Controllers\KetuaKelas;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $user = Auth::user();

        // Get kelas yang dikelola oleh ketua kelas ini
        // Asumsi: user memiliki relasi dengan kelas melalui guru
        $guru = $user->guru;
        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan');
        }

        // Ambil kelas yang diajar oleh guru ini (sebagai wali kelas)
        $kelas = $guru->kelas()->first(); // Asumsi guru sebagai wali kelas

        if (!$kelas) {
            // Jika tidak ada kelas, ambil dari jadwal
            $kelas = Kelas::whereHas('jadwals', function($query) use ($guru) {
                $query->where('guru_id', $guru->id);
            })->first();
        }

        if (!$kelas) {
            return view('ketua-kelas.dashboard', [
                'kelas' => null,
                'message' => 'Anda belum ditugaskan sebagai wali kelas atau pengajar kelas manapun.'
            ]);
        }

        // Jadwal kelas hari ini
        $dayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;
        $jadwalHariIni = Jadwal::with(['guru.user', 'mataPelajaran'])
            ->where('kelas_id', $kelas->id)
            ->where('hari', $dayOfWeek)
            
            ->orderBy('jam_mulai')
            ->get();

        // Statistik kehadiran guru di kelas ini hari ini
        $totalJadwalHariIni = $jadwalHariIni->count();
        $guruSudahAbsen = 0;
        $guruBelumAbsen = 0;
        $guruIzin = 0;
        $guruTerlambat = 0;

        foreach ($jadwalHariIni as $jadwal) {
            $absensi = Absensi::where('guru_id', $jadwal->guru_id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($absensi) {
                if ($absensi->status === 'terlambat') {
                    $guruTerlambat++;
                }
                $guruSudahAbsen++;
            } else {
                // Check izin
                $izin = $jadwal->guru->izins()
                    ->whereDate('tanggal_mulai', '<=', $today)
                    ->whereDate('tanggal_selesai', '>=', $today)
                    ->where('status', 'approved')
                    ->exists();

                if ($izin) {
                    $guruIzin++;
                } else {
                    $guruBelumAbsen++;
                }
            }
        }

        // Jadwal minggu ini (7 hari ke depan)
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        $jadwalMingguIni = [];
        for ($day = 1; $day <= 7; $day++) {
            $jadwalHari = Jadwal::with(['guru.user', 'mataPelajaran'])
                ->where('kelas_id', $kelas->id)
                ->where('hari', $day)
                
                ->orderBy('jam_mulai')
                ->get();

            $jadwalMingguIni[$day] = [
                'nama_hari' => $this->getDayName($day),
                'tanggal' => $startOfWeek->copy()->addDays($day - 1)->format('d/m/Y'),
                'jadwal' => $jadwalHari
            ];
        }

        // Riwayat kehadiran guru di kelas (7 hari terakhir)
        $riwayatKehadiran = Absensi::with(['guru.user'])
            ->whereHas('guru.jadwals', function($query) use ($kelas) {
                $query->where('kelas_id', $kelas->id);
            })
            ->whereBetween('tanggal', [$today->copy()->subDays(6), $today])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->get();

        // Guru yang sering terlambat di kelas ini (bulan ini)
        $guruSeringTerlambat = Absensi::select('guru_id', \DB::raw('COUNT(*) as total_terlambat'))
            ->with('guru.user')
            ->whereHas('guru.jadwals', function($query) use ($kelas) {
                $query->where('kelas_id', $kelas->id);
            })
            ->where('status', 'terlambat')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->groupBy('guru_id')
            ->orderBy('total_terlambat', 'desc')
            ->limit(5)
            ->get();

        return view('ketua-kelas.dashboard', compact(
            'kelas',
            'jadwalHariIni',
            'totalJadwalHariIni',
            'guruSudahAbsen',
            'guruBelumAbsen',
            'guruIzin',
            'guruTerlambat',
            'jadwalMingguIni',
            'riwayatKehadiran',
            'guruSeringTerlambat'
        ));
    }

    /**
     * Get day name in Indonesian
     */
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
