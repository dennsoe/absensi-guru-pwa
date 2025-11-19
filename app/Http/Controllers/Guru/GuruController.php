<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Guru, Absensi, JadwalMengajar, IzinCuti};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GuruController extends Controller
{
    /**
     * Dashboard Guru - Tampilkan jadwal hari ini dan status absensi
     */
    public function dashboard()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('login')
                ->with('error', 'Anda tidak memiliki akses sebagai guru.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
        $tanggal_hari_ini = Carbon::today();
        $jam_sekarang = Carbon::now();

        // Ambil semua jadwal mengajar hari ini untuk guru ini
        $jadwal_hari_ini = JadwalMengajar::where('guru_id', $guru->id)
            ->where('hari', $hari_ini)
            
            ->with(['kelas', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        // Tambahkan informasi status absensi untuk setiap jadwal
        $jadwal_hari_ini->each(function($jadwal) use ($guru, $tanggal_hari_ini, $jam_sekarang) {
            // Cek apakah sudah absen untuk jadwal ini
            $jadwal->absensi_record = Absensi::where('guru_id', $guru->id)
                ->where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal_hari_ini)
                ->first();

            // Tentukan status jadwal (akan datang, berlangsung, selesai)
            $jam_mulai = Carbon::parse($jadwal->jam_mulai);
            $jam_selesai = Carbon::parse($jadwal->jam_selesai);

            if ($jam_sekarang->lt($jam_mulai)) {
                $jadwal->status_jadwal = 'akan_datang';
                $jadwal->menit_tersisa = $jam_sekarang->diffInMinutes($jam_mulai, false);
            } elseif ($jam_sekarang->between($jam_mulai, $jam_selesai)) {
                $jadwal->status_jadwal = 'berlangsung';
            } else {
                $jadwal->status_jadwal = 'selesai';
            }
        });

        // Hitung total absensi hari ini
        $total_absensi_hari_ini = Absensi::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal_hari_ini)
            ->count();

        // Statistik absensi bulan ini
        $bulan_ini = Carbon::now()->month;
        $tahun_ini = Carbon::now()->year;

        $absensi_bulan_ini = Absensi::where('guru_id', $guru->id)
            ->whereMonth('tanggal', $bulan_ini)
            ->whereYear('tanggal', $tahun_ini)
            ->get();

        $statistik = [
            'total' => $absensi_bulan_ini->count(),
            'hadir' => $absensi_bulan_ini->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi_bulan_ini->where('status_kehadiran', 'terlambat')->count(),
            'izin' => $absensi_bulan_ini->whereIn('status_kehadiran', ['izin', 'sakit', 'cuti'])->count(),
            'alpha' => $absensi_bulan_ini->where('status_kehadiran', 'alpha')->count(),
        ];

        // Hitung persentase kehadiran
        $statistik['persentase'] = $statistik['total'] > 0
            ? round((($statistik['hadir'] + $statistik['terlambat']) / $statistik['total']) * 100, 1)
            : 0;

        // Alert: Jadwal yang perlu perhatian
        $alerts = [];

        // 1. Jadwal yang sedang berlangsung tapi belum absen (URGENT)
        $jadwal_berlangsung = $jadwal_hari_ini->first(function($jadwal) {
            return $jadwal->status_jadwal === 'berlangsung' && !$jadwal->absensi_record;
        });

        if ($jadwal_berlangsung) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'exclamation-triangle-fill',
                'title' => 'Jadwal Sedang Berlangsung!',
                'message' => "Anda memiliki jadwal {$jadwal_berlangsung->mataPelajaran->nama_mapel} di kelas {$jadwal_berlangsung->kelas->nama_kelas} yang sedang berlangsung. Segera lakukan absensi!",
                'jadwal' => $jadwal_berlangsung,
            ];
        }

        // 2. Jadwal yang akan datang dalam 30 menit
        $jadwal_upcoming = $jadwal_hari_ini->first(function($jadwal) {
            return $jadwal->status_jadwal === 'akan_datang'
                && isset($jadwal->menit_tersisa)
                && $jadwal->menit_tersisa <= 30;
        });

        if ($jadwal_upcoming) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'clock-fill',
                'title' => 'Jadwal Akan Dimulai!',
                'message' => "Anda memiliki jadwal {$jadwal_upcoming->mataPelajaran->nama_mapel} di kelas {$jadwal_upcoming->kelas->nama_kelas} dalam {$jadwal_upcoming->menit_tersisa} menit.",
                'jadwal' => $jadwal_upcoming,
            ];
        }

        // 3. Jadwal yang sudah lewat tapi belum absen (KRITIS)
        $jadwal_terlewat = $jadwal_hari_ini->filter(function($jadwal) {
            return $jadwal->status_jadwal === 'selesai' && !$jadwal->absensi_record;
        });

        if ($jadwal_terlewat->isNotEmpty()) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'x-circle-fill',
                'title' => 'Jadwal Terlewat Tanpa Absensi!',
                'message' => "Anda memiliki {$jadwal_terlewat->count()} jadwal yang sudah selesai namun belum melakukan absensi. Hubungi Guru Piket untuk absensi manual.",
                'count' => $jadwal_terlewat->count(),
            ];
        }

        // Izin/Cuti yang pending approval
        $izin_pending = IzinCuti::where('guru_id', $guru->id)
            ->where('status', 'pending')
            ->count();

        return view('guru.dashboard', [
            'guru' => $guru,
            'hari_ini' => $hari_ini,
            'tanggal_hari_ini' => $tanggal_hari_ini,
            'jadwal_hari_ini' => $jadwal_hari_ini,
            'total_absensi_hari_ini' => $total_absensi_hari_ini,
            'statistik' => $statistik,
            'alerts' => $alerts,
            'izin_pending' => $izin_pending,
        ]);
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

    /**
     * Halaman Absensi Keluar
     */
    public function absensiKeluar()
    {
        $guru = Auth::user()->guru;

        // Cek apakah ada absensi masuk hari ini
        $absensiMasukHariIni = Absensi::where('guru_id', $guru->id)
            ->whereDate('tanggal', Carbon::today())
            ->whereNotNull('jam_masuk')
            ->first();

        return view('guru.absensi.keluar', [
            'absensiMasukHariIni' => $absensiMasukHariIni
        ]);
    }

    /**
     * Proses Absensi Keluar
     */
    public function prosesAbsensiKeluar(Request $request)
    {
        $guru = Auth::user()->guru;

        $request->validate([
            'absensi_id' => 'required|exists:absensis,id',
        ]);

        $absensi = Absensi::findOrFail($request->absensi_id);

        // Validasi kepemilikan absensi
        if ($absensi->guru_id !== $guru->id) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi tidak valid'
            ], 403);
        }

        // Validasi sudah absen keluar
        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi keluar'
            ], 400);
        }

        // Update jam keluar
        $absensi->jam_keluar = Carbon::now();
        $absensi->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Absensi keluar berhasil dicatat'
            ]);
        }

        return redirect()->route('guru.dashboard')
            ->with('success', 'Absensi keluar berhasil dicatat');
    }
}
