<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar, IzinCuti, GuruPengganti};
use Illuminate\Support\Facades\Auth;

class GuruPiketController extends Controller
{
    /**
     * Dashboard Guru Piket
     */
    public function dashboard()
    {
        // Get hari ini
        $hari_ini = ucfirst(now()->locale('id')->dayName);

        // Jadwal hari ini
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
                                        ->where('status', 'aktif')
                                        ->with(['guru', 'kelas', 'mataPelajaran'])
                                        ->orderBy('jam_mulai')
                                        ->get();

        // Count statistics
        $guru_hadir = Absensi::whereDate('tanggal', today())
                             ->where('status_kehadiran', 'hadir')
                             ->count();

        $guru_terlambat = Absensi::whereDate('tanggal', today())
                                 ->where('status_kehadiran', 'terlambat')
                                 ->count();

        $guru_belum_hadir = $jadwal_hari_ini->count() - Absensi::whereDate('tanggal', today())
                                                                ->whereIn('status_kehadiran', ['hadir', 'terlambat'])
                                                                ->count();

        $total_guru_mengajar = $jadwal_hari_ini->count();

        // Alert: guru belum absen 10 menit before jam mengajar
        $waktu_sekarang = now();
        $guru_belum_absen_alert = $jadwal_hari_ini->filter(function($jadwal) use ($waktu_sekarang) {
            $jam_mengajar = \Carbon\Carbon::parse($jadwal->jam_mulai);
            $batas_alert = $jam_mengajar->subMinutes(10);

            if ($waktu_sekarang >= $batas_alert && $waktu_sekarang <= $jam_mengajar) {
                $absensi = $jadwal->absensi()->whereDate('tanggal', today())->first();
                return !$absensi;
            }
            return false;
        });

        $data = compact(
            'jadwal_hari_ini',
            'guru_hadir',
            'guru_terlambat',
            'guru_belum_hadir',
            'total_guru_mengajar',
            'guru_belum_absen_alert'
        );

        return view('guru-piket.dashboard', $data);
    }

    /**
     * Monitoring Absensi Real-time
     */
    public function monitoringAbsensi()
    {
        $absensi = Absensi::whereDate('tanggal', today())
                          ->with(['guru', 'jadwalMengajar.kelas', 'jadwalMengajar.mataPelajaran'])
                          ->latest('jam_masuk')
                          ->get();

        return view('piket.monitoring', compact('absensi'));
    }

    /**
     * Input Absensi Manual (untuk guru yang tidak bisa scan QR)
     */
    public function inputAbsensiManual()
    {
        $guru = Guru::whereHas('user', function($q) {
                        $q->where('status', 'aktif');
                    })
                    ->whereDoesntHave('absensi', function($q) {
                        $q->whereDate('tanggal', today());
                    })
                    ->get();

        $jadwal = JadwalMengajar::where('hari', ucfirst(now()->locale('id')->dayName))
                                ->where('status', 'aktif')
                                ->with(['guru', 'kelas', 'mataPelajaran'])
                                ->get();

        return view('piket.absensi-manual', compact('guru', 'jadwal'));
    }

    public function storeAbsensiManual(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alpha,dinas_luar',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $validated['tanggal'] = today();
        $validated['jam_masuk'] = now()->format('H:i:s');
        $validated['metode_absensi'] = 'manual';
        $validated['dibuat_oleh'] = Auth::id();

        Absensi::create($validated);

        return redirect()->route('piket.monitoring')
                        ->with('success', 'Absensi manual berhasil dicatat.');
    }
}
