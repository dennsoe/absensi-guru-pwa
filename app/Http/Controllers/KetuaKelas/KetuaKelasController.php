<?php

namespace App\Http\Controllers\KetuaKelas;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalMengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KetuaKelasController extends Controller
{
    /**
     * Dashboard Ketua Kelas
     */
    public function dashboard()
    {
        // Data dummy untuk sementara (nanti akan diganti dengan data real)
        $data = [
            'scan_hari_ini' => 0,
            'scan_valid' => 0,
            'scan_invalid' => 0,
            'scan_minggu_ini' => 0,
            'riwayat_scan' => [],
            'jadwal_kelas' => [],
        ];

        return view('ketua-kelas.dashboard', $data);
    }

    /**
     * Tampilkan halaman generate QR (ketua kelas generate untuk discan guru)
     */
    public function generateQr()
    {
        // Ambil data kelas dari user ketua kelas
        // Untuk sementara menggunakan data dummy atau dari user profile
        $kelasId = auth()->user()->kelas_id ?? 1; // Harus ada relasi user ke kelas

        $data = [
            'kelas_id' => $kelasId,
            'ketua_kelas_nama' => auth()->user()->nama,
        ];

        return view('ketua-kelas.generate-qr', $data);
    }

    /**
     * Get statistik scan untuk kelas ini
     */
    public function statistikScan()
    {
        $kelasId = auth()->user()->kelas_id ?? 1;

        // Count absensi hari ini untuk kelas ini
        $today = Carbon::today();

        $total = Absensi::whereHas('jadwal', function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->whereDate('tanggal', $today)
            ->count();

        $hadir = Absensi::whereHas('jadwal', function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->whereDate('tanggal', $today)
            ->where('status_kehadiran', 'hadir')
            ->count();

        $terlambat = Absensi::whereHas('jadwal', function($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->whereDate('tanggal', $today)
            ->where('status_kehadiran', 'terlambat')
            ->count();

        return response()->json([
            'total' => $total,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
        ]);
    }

    /**
     * Get riwayat scan hari ini
     */
    public function riwayatScan()
    {
        // Get kelas dari user ketua kelas
        // Asumsi: user ketua kelas memiliki relasi ke kelas_id
        // Untuk sementara ambil semua absensi hari ini

        $riwayat = Absensi::with(['guru', 'jadwal'])
            ->whereDate('tanggal', Carbon::today())
            ->where('metode_absensi', 'qr')
            ->orderBy('jam_absen', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'jam_absen' => Carbon::parse($item->jam_absen)->format('H:i'),
                    'guru' => [
                        'nama' => $item->guru->nama,
                    ],
                    'jadwal' => [
                        'mata_pelajaran' => $item->jadwal->mata_pelajaran,
                    ],
                    'status_kehadiran' => $item->status_kehadiran,
                    'keterangan' => $item->keterangan,
                ];
            });

        return response()->json($riwayat);
    }

    /**
     * Get statistik scan hari ini
     */
    public function statistik()
    {
        // Count successful scans (saved to database)
        $validScans = Absensi::whereDate('tanggal', Carbon::today())
            ->where('metode_absensi', 'qr')
            ->where('verified_by', Auth::user()->id)
            ->count();

        // For invalid scans, we could track in a separate table or session
        // For now, return 0
        $invalidScans = 0;

        return response()->json([
            'valid' => $validScans,
            'invalid' => $invalidScans,
        ]);
    }

    /**
     * Get jadwal kelas hari ini
     */
    public function jadwal()
    {
        $hari = Carbon::now()->locale('id')->isoFormat('dddd');

        // Asumsi: ketua kelas memiliki kelas_id
        // Untuk sementara ambil semua jadwal hari ini
        $jadwal = JadwalMengajar::with(['guru', 'kelas'])
            ->where('hari', $hari)
            ->where('is_active', true)
            ->orderBy('jam_mulai', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'mata_pelajaran' => $item->mata_pelajaran,
                    'guru' => [
                        'nama' => $item->guru->nama,
                    ],
                    'jam_mulai' => Carbon::parse($item->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($item->jam_selesai)->format('H:i'),
                    'ruangan' => $item->ruangan,
                ];
            });

        return response()->json($jadwal);
    }

    /**
     * Riwayat absensi kelas (history page)
     */
    public function riwayat()
    {
        return view('ketua-kelas.riwayat');
    }
}
