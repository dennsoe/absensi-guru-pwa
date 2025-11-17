<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Guru, Absensi, JadwalMengajar, IzinCuti};
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    /**
     * Dashboard Guru
     */
    public function dashboard()
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $hari_ini = ucfirst(now()->locale('id')->dayName);
        $tanggal_hari_ini = today();
        $jam_sekarang = now();

        // Jadwal hari ini
        $jadwal_hari_ini = JadwalMengajar::where('guru_id', $guru->id)
                                           ->where('hari', $hari_ini)
                                           ->where('status', 'aktif')
                                           ->with(['kelas', 'mataPelajaran'])
                                           ->orderBy('jam_mulai')
                                           ->get();

        // Absensi hari ini
        $absensi_hari_ini = Absensi::where('guru_id', $guru->id)
                                    ->whereDate('tanggal', $tanggal_hari_ini)
                                    ->count();

        // Absensi bulan ini dengan detail
        $absensi_bulan_ini = Absensi::where('guru_id', $guru->id)
                                      ->whereMonth('tanggal', now()->month)
                                      ->whereYear('tanggal', now()->year)
                                      ->get();

        // Statistik kehadiran
        $total_hadir = $absensi_bulan_ini->whereIn('status_kehadiran', ['hadir'])->count();
        $total_terlambat = $absensi_bulan_ini->where('status_kehadiran', 'terlambat')->count();
        $total_izin = $absensi_bulan_ini->whereIn('status_kehadiran', ['izin', 'sakit'])->count();
        $total_alpha = $absensi_bulan_ini->where('status_kehadiran', 'alpha')->count();

        // Cek jadwal yang akan datang (dalam 30 menit)
        $jadwal_upcoming = $jadwal_hari_ini->filter(function($jadwal) use ($jam_sekarang) {
            $jam_mulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
            $selisih_menit = $jam_sekarang->diffInMinutes($jam_mulai, false);
            return $selisih_menit > 0 && $selisih_menit <= 30;
        })->first();

        // Cek jadwal yang sedang berlangsung
        $jadwal_berlangsung = $jadwal_hari_ini->filter(function($jadwal) use ($jam_sekarang) {
            $jam_mulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
            $jam_selesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
            return $jam_sekarang->between($jam_mulai, $jam_selesai);
        })->first();

        // Cek apakah sudah absen untuk jadwal yang sedang berlangsung
        $sudah_absen_jadwal_berlangsung = false;
        if ($jadwal_berlangsung) {
            $sudah_absen_jadwal_berlangsung = Absensi::where('guru_id', $guru->id)
                                                     ->where('jadwal_id', $jadwal_berlangsung->id)
                                                     ->whereDate('tanggal', $tanggal_hari_ini)
                                                     ->exists();
        }

        // CEK JADWAL YANG SUDAH LEWAT TAPI BELUM ABSEN (KRITIS!)
        $jadwal_terlewat_belum_absen = $jadwal_hari_ini->filter(function($jadwal) use ($jam_sekarang, $guru, $tanggal_hari_ini) {
            $jam_selesai = \Carbon\Carbon::parse($jadwal->jam_selesai);

            // Jadwal sudah selesai (lewat)
            if ($jam_sekarang->greaterThan($jam_selesai)) {
                // Cek apakah ada absensi untuk jadwal ini
                $sudah_absen = Absensi::where('guru_id', $guru->id)
                                      ->where('jadwal_id', $jadwal->id)
                                      ->whereDate('tanggal', $tanggal_hari_ini)
                                      ->exists();

                return !$sudah_absen; // Return TRUE jika belum absen
            }

            return false;
        });

        // Ambil jadwal terlewat pertama (paling urgent)
        $jadwal_terlewat_pertama = $jadwal_terlewat_belum_absen->first();

        $data = [
            'guru' => $guru,
            'hari_ini' => $hari_ini,
            'jadwal_hari_ini' => $jadwal_hari_ini,
            'absensi_hari_ini' => $absensi_hari_ini,
            'absensi_bulan_ini' => $absensi_bulan_ini,
            'total_hadir' => $total_hadir,
            'total_terlambat' => $total_terlambat,
            'total_izin' => $total_izin,
            'total_alpha' => $total_alpha,
            'jadwal_upcoming' => $jadwal_upcoming,
            'jadwal_berlangsung' => $jadwal_berlangsung,
            'sudah_absen_jadwal_berlangsung' => $sudah_absen_jadwal_berlangsung,
            'jadwal_terlewat_belum_absen' => $jadwal_terlewat_belum_absen,
            'jadwal_terlewat_pertama' => $jadwal_terlewat_pertama,
        ];

        return view('guru.dashboard', $data);
    }

    /**
     * Riwayat Absensi Guru
     */
    public function riwayatAbsensi(Request $request)
    {
        $guru = Auth::user()->guru;

        $query = Absensi::where('guru_id', $guru->id)
                        ->with(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $absensi = $query->latest('tanggal')->paginate(20);

        return view('guru.absensi.riwayat', compact('absensi'));
    }

    /**
     * Detail Absensi
     */
    public function detailAbsensi(Absensi $absensi)
    {
        // Pastikan absensi milik guru yang login
        if ($absensi->guru_id !== Auth::user()->guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        $absensi->load(['jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran', 'ketuaKelas']);

        return view('guru.absensi.detail', compact('absensi'));
    }
}
