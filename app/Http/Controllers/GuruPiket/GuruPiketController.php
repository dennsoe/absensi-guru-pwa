<?php

namespace App\Http\Controllers\GuruPiket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, Guru, JadwalMengajar, Kelas, MataPelajaran};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GuruPiketController extends Controller
{
    /**
     * Dashboard Guru Piket - Monitoring Real-time
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Validasi role
        if ($user->role !== 'guru_piket') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Hanya Guru Piket yang dapat mengakses halaman ini.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
        $tanggal = Carbon::today();

        // JADWAL HARI INI dengan status absensi
        $jadwal_hari_ini = JadwalMengajar::where('hari', $hari_ini)
            
            ->with(['guru.user', 'kelas', 'mataPelajaran', 'absensi' => function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            }])
            ->orderBy('jam_mulai')
            ->get();

        // Deteksi status untuk setiap jadwal
        $jam_sekarang = Carbon::now();
        $jadwal_hari_ini = $jadwal_hari_ini->map(function($jadwal) use ($jam_sekarang, $tanggal) {
            $jam_mulai = Carbon::createFromFormat('H:i:s', $jadwal->jam_mulai);
            $jam_selesai = Carbon::createFromFormat('H:i:s', $jadwal->jam_selesai);

            // Tentukan status waktu
            if ($jam_sekarang->lt($jam_mulai)) {
                $jadwal->status_waktu = 'akan_datang';
            } elseif ($jam_sekarang->between($jam_mulai, $jam_selesai)) {
                $jadwal->status_waktu = 'berlangsung';
            } else {
                $jadwal->status_waktu = 'selesai';
            }

            // Cek absensi
            $absensi = $jadwal->absensi->first();
            $jadwal->sudah_absen = $absensi ? true : false;
            $jadwal->status_absensi = $absensi->status ?? null;
            $jadwal->metode_absensi = $absensi->metode_absensi ?? null;
            $jadwal->jam_absen = $absensi->jam_absen ?? null;
            $jadwal->absensi_data = $absensi;

            return $jadwal;
        });

        // STATISTIK
        $total_jadwal = $jadwal_hari_ini->count();
        $sudah_absen = $jadwal_hari_ini->where('sudah_absen', true)->count();
        $belum_absen = $total_jadwal - $sudah_absen;

        $hadir = Absensi::whereDate('tanggal', $tanggal)
            ->where('status', 'hadir')
            ->count();

        $terlambat = Absensi::whereDate('tanggal', $tanggal)
            ->where('status', 'terlambat')
            ->count();

        $izin_sakit = Absensi::whereDate('tanggal', $tanggal)
            ->whereIn('status', ['izin', 'sakit'])
            ->count();

        // ALERT: Jadwal yang perlu perhatian
        $alerts = [
            'critical' => [], // Jadwal berlangsung, belum absen
            'warning' => [],  // Jadwal akan datang (<30 menit), belum absen
            'info' => [],     // Jadwal selesai, belum absen
        ];

        foreach ($jadwal_hari_ini as $jadwal) {
            if (!$jadwal->sudah_absen) {
                $jam_mulai = Carbon::createFromFormat('H:i:s', $jadwal->jam_mulai);
                $selisih_menit = $jam_sekarang->diffInMinutes($jam_mulai, false);

                if ($jadwal->status_waktu === 'berlangsung') {
                    $alerts['critical'][] = [
                        'jadwal' => $jadwal,
                        'pesan' => "Jadwal sedang berlangsung! Guru belum hadir.",
                    ];
                } elseif ($jadwal->status_waktu === 'akan_datang' && $selisih_menit <= 30) {
                    $alerts['warning'][] = [
                        'jadwal' => $jadwal,
                        'pesan' => "Jadwal dimulai dalam {$selisih_menit} menit. Guru belum hadir.",
                    ];
                } elseif ($jadwal->status_waktu === 'selesai') {
                    $alerts['info'][] = [
                        'jadwal' => $jadwal,
                        'pesan' => "Jadwal sudah selesai tanpa absensi (Alpha).",
                    ];
                }
            }
        }

        return view('guru-piket.dashboard', [
            'jadwal_hari_ini' => $jadwal_hari_ini,
            'statistik' => [
                'total_jadwal' => $total_jadwal,
                'sudah_absen' => $sudah_absen,
                'belum_absen' => $belum_absen,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => $izin_sakit,
                'persentase_kehadiran' => $total_jadwal > 0 ? round(($sudah_absen / $total_jadwal) * 100, 1) : 0,
            ],
            'alerts' => $alerts,
            'hari_ini' => $hari_ini,
            'tanggal' => $tanggal->format('d F Y'),
        ]);
    }

    /**
     * Monitoring Absensi Real-time (AJAX Endpoint)
     */
    public function monitoringAbsensi(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
            $tanggal = Carbon::today();

            $jadwal = JadwalMengajar::where('hari', $hari_ini)
                
                ->with(['guru.user', 'kelas', 'mataPelajaran', 'absensi' => function($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                }])
                ->orderBy('jam_mulai')
                ->get();

            $data = $jadwal->map(function($item) {
                $absensi = $item->absensi->first();
                $jam_mulai = Carbon::createFromFormat('H:i:s', $item->jam_mulai);
                $jam_sekarang = Carbon::now();

                // Status waktu
                if ($jam_sekarang->lt($jam_mulai)) {
                    $status_waktu = 'akan_datang';
                } elseif ($jam_sekarang->between($jam_mulai, Carbon::createFromFormat('H:i:s', $item->jam_selesai))) {
                    $status_waktu = 'berlangsung';
                } else {
                    $status_waktu = 'selesai';
                }

                return [
                    'id' => $item->id,
                    'jam_mulai' => $item->jam_mulai,
                    'jam_selesai' => $item->jam_selesai,
                    'guru_nama' => $item->guru->user->name ?? '-',
                    'guru_nip' => $item->guru->nip ?? '-',
                    'kelas' => $item->kelas->nama_kelas ?? '-',
                    'mata_pelajaran' => $item->mataPelajaran->nama_mapel ?? '-',
                    'status_waktu' => $status_waktu,
                    'sudah_absen' => $absensi ? true : false,
                    'jam_absen' => $absensi ? $absensi->jam_absen : null,
                    'status_absensi' => $absensi ? $absensi->status : null,
                    'metode_absensi' => $absensi ? $absensi->metode_absensi : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => Carbon::now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('guru-piket.dashboard');
    }

    /**
     * Form Input Absensi Manual
     */
    public function inputAbsensiManual()
    {
        $user = Auth::user();

        if ($user->role !== 'guru_piket') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $hari_ini = ucfirst(Carbon::now()->locale('id')->dayName);
        $tanggal = Carbon::today();

        // Jadwal yang belum diabsen
        $jadwal_belum_absen = JadwalMengajar::where('hari', $hari_ini)
            
            ->whereDoesntHave('absensi', function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            })
            ->with(['guru.user', 'kelas', 'mataPelajaran'])
            ->orderBy('jam_mulai')
            ->get();

        return view('guru-piket.input-manual', [
            'jadwal_belum_absen' => $jadwal_belum_absen,
            'hari_ini' => $hari_ini,
            'tanggal' => $tanggal->format('d F Y'),
        ]);
    }

    /**
     * Proses Input Absensi Manual oleh Guru Piket
     */
    public function storeAbsensiManual(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();

            if ($user->role !== 'guru_piket') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Guru Piket yang dapat input absensi manual.',
                ], 403);
            }

            $jadwal = JadwalMengajar::with(['guru', 'kelas'])->find($request->jadwal_id);

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan.',
                ], 404);
            }

            // Cek sudah absen atau belum
            $sudah_absen = Absensi::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', Carbon::today())
                ->exists();

            if ($sudah_absen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal ini sudah memiliki absensi hari ini.',
                ], 400);
            }

            // Simpan absensi manual
            $absensi = Absensi::create([
                'jadwal_id' => $jadwal->id,
                'tanggal' => Carbon::today(),
                'jam_absen' => Carbon::now()->format('H:i:s'),
                'status' => $request->status,
                'metode_absensi' => 'manual',
                'validasi_guru_piket' => true,
                'validasi_guru_piket_user_id' => $user->id,
                'validasi_guru_piket_at' => Carbon::now(),
                'keterangan' => $request->keterangan ?? "Input manual oleh Guru Piket: {$user->name}",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Absensi manual berhasil disimpan untuk {$jadwal->guru->user->name} ({$jadwal->kelas->nama_kelas}).",
                'data' => [
                    'absensi_id' => $absensi->id,
                    'guru_nama' => $jadwal->guru->user->name,
                    'kelas' => $jadwal->kelas->nama_kelas,
                    'status' => $absensi->status,
                    'jam_absen' => $absensi->jam_absen,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error store absensi manual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Laporan Harian
     */
    public function laporanHarian(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'guru_piket') {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak.');
        }

        $tanggal = $request->input('tanggal', Carbon::today());
        if (is_string($tanggal)) {
            $tanggal = Carbon::parse($tanggal);
        }

        $hari = ucfirst($tanggal->locale('id')->dayName);

        // Jadwal pada tanggal tersebut
        $jadwal = JadwalMengajar::where('hari', $hari)
            
            ->with(['guru.user', 'kelas', 'mataPelajaran', 'absensi' => function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            }])
            ->orderBy('jam_mulai')
            ->get();

        // Statistik
        $total_jadwal = $jadwal->count();
        $hadir = 0;
        $terlambat = 0;
        $izin = 0;
        $sakit = 0;
        $alpha = 0;

        foreach ($jadwal as $j) {
            $absensi = $j->absensi->first();
            if ($absensi) {
                switch ($absensi->status) {
                    case 'hadir': $hadir++; break;
                    case 'terlambat': $terlambat++; break;
                    case 'izin': $izin++; break;
                    case 'sakit': $sakit++; break;
                    case 'alpha': $alpha++; break;
                }
            } else {
                // Jika jadwal sudah lewat tapi tidak ada absensi, hitung sebagai alpha
                $jam_selesai = Carbon::createFromFormat('H:i:s', $j->jam_selesai);
                if (Carbon::now()->greaterThan($jam_selesai)) {
                    $alpha++;
                }
            }
        }

        return view('guru-piket.laporan-harian', [
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'hari' => $hari,
            'statistik' => [
                'total_jadwal' => $total_jadwal,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'persentase_kehadiran' => $total_jadwal > 0 ? round((($hadir + $terlambat) / $total_jadwal) * 100, 1) : 0,
            ],
        ]);
    }
}
