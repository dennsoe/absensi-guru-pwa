<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalMengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // Konstanta GPS Sekolah (ganti dengan koordinat sekolah yang sebenarnya)
    const SEKOLAH_LAT = -7.797068;
    const SEKOLAH_LNG = 110.370529;
    const RADIUS_METER = 200;

    /**
     * Tampilkan halaman scan QR Code (guru scan QR dari ketua kelas)
     */
    public function scanQr()
    {
        return view('guru.absensi.scan-qr');
    }

    /**
     * Tampilkan halaman absensi Selfie
     */
    public function selfie()
    {
        return view('guru.absensi.selfie');
    }

    /**
     * Proses absensi QR Code (guru scan QR dari ketua kelas)
     */
    public function prosesAbsensiQr(Request $request)
    {
        try {
            // Decode QR data (dari ketua kelas)
            $qrString = $request->input('qr_data');
            $qrData = json_decode(base64_decode($qrString), true);

            // Validasi struktur data
            if (!isset($qrData['kelas_id']) || !isset($qrData['timestamp'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format QR Code tidak valid'
                ], 400);
            }

            // Validasi expiry (5 menit)
            $now = now()->timestamp * 1000; // Convert to milliseconds
            if ($now > $qrData['expires']) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah kadaluarsa. Minta ketua kelas untuk refresh QR Code.'
                ], 400);
            }

            // Validasi lokasi GPS (guru yang scan)
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                self::SEKOLAH_LAT,
                self::SEKOLAH_LNG
            );

            if ($distance > self::RADIUS_METER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi Anda terlalu jauh dari sekolah (' . round($distance) . 'm). Maksimal ' . self::RADIUS_METER . 'm.'
                ], 400);
            }

            // Get guru dari auth
            $guruId = Auth::user()->guru_id;

            if (!$guruId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki profil guru'
                ], 400);
            }

            // Cari jadwal hari ini untuk guru di kelas ini
            $hari = Carbon::now()->locale('id')->isoFormat('dddd');
            $jadwal = JadwalMengajar::where('guru_id', $guruId)
                ->where('kelas_id', $qrData['kelas_id'])
                ->where('hari', $hari)
                ->where('is_active', true)
                ->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal mengajar untuk Anda di kelas ini hari ini'
                ], 404);
            }

            // Cek apakah sudah absen
            $existingAbsensi = Absensi::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', Carbon::today())
                ->first();

            if ($existingAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi untuk jadwal ini'
                ], 400);
            }

            // Tentukan status kehadiran berdasarkan waktu
            $jamSekarang = Carbon::now();
            $jamMulai = Carbon::parse($jadwal->jam_mulai);
            $batasTerlambat = $jamMulai->copy()->addMinutes(15);

            $statusKehadiran = 'hadir';
            $keterangan = null;

            if ($jamSekarang->gt($batasTerlambat)) {
                $statusKehadiran = 'terlambat';
                $menitTerlambat = $jamSekarang->diffInMinutes($jamMulai);
                $keterangan = "Terlambat {$menitTerlambat} menit";
            }

            // Simpan absensi
            $absensi = Absensi::create([
                'jadwal_id' => $jadwal->id,
                'guru_id' => $guruId,
                'tanggal' => Carbon::today(),
                'jam_absen' => Carbon::now()->format('H:i:s'),
                'status_kehadiran' => $statusKehadiran,
                'metode_absensi' => 'qr',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'keterangan' => $keterangan,
                'foto_selfie' => null,
                'verified_by' => $qrData['kelas_id'], // Kelas sebagai verifikasi
            ]);

            // Load relasi untuk response
            $absensi->load(['guru', 'jadwal', 'jadwal.kelas']);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
                'data' => [
                    'kelas' => $absensi->jadwal->kelas->nama_kelas ?? 'N/A',
                    'mata_pelajaran' => $absensi->jadwal->mata_pelajaran,
                    'status' => $statusKehadiran,
                    'status_text' => $statusKehadiran === 'hadir' ? 'Hadir' : 'Terlambat',
                    'jam_absen' => $absensi->jam_absen,
                    'keterangan' => $keterangan
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error proses absensi QR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses absensi Selfie
     */
    public function prosesAbsensiSelfie(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
            'foto_selfie' => 'required|string', // Base64 encoded image
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            // Validasi lokasi GPS
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                self::SEKOLAH_LAT,
                self::SEKOLAH_LNG
            );

            if ($distance > self::RADIUS_METER) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada terlalu jauh dari sekolah (' . round($distance) . 'm). Maksimal ' . self::RADIUS_METER . 'm.'
                ], 400);
            }

            // Cari jadwal
            $jadwal = JadwalMengajar::findOrFail($request->jadwal_id);

            // Cek apakah sudah absen
            $existingAbsensi = Absensi::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', Carbon::today())
                ->first();

            if ($existingAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi untuk jadwal ini'
                ], 400);
            }

            // Tentukan status kehadiran berdasarkan waktu
            $jamSekarang = Carbon::now();
            $jamMulai = Carbon::parse($jadwal->jam_mulai);
            $batasTerlambat = $jamMulai->copy()->addMinutes(15);

            $statusKehadiran = 'hadir';
            $keterangan = null;

            if ($jamSekarang->gt($batasTerlambat)) {
                $statusKehadiran = 'terlambat';
                $menitTerlambat = $jamSekarang->diffInMinutes($jamMulai);
                $keterangan = "Terlambat {$menitTerlambat} menit";
            }

            // Simpan foto selfie
            $fotoSelfie = $this->saveSelfie($request->foto_selfie, Auth::user()->guru_id);

            // Simpan absensi
            $absensi = Absensi::create([
                'jadwal_id' => $jadwal->id,
                'guru_id' => Auth::user()->guru_id,
                'tanggal' => Carbon::today(),
                'jam_absen' => Carbon::now()->format('H:i:s'),
                'status_kehadiran' => $statusKehadiran,
                'metode_absensi' => 'selfie',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'keterangan' => $keterangan,
                'foto_selfie' => $fotoSelfie,
                'verified_by' => null,
            ]);

            $absensi->load(['guru', 'jadwal']);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
                'data' => [
                    'status' => $statusKehadiran,
                    'status_text' => $statusKehadiran === 'hadir' ? 'Hadir' : 'Terlambat',
                    'jam_absen' => $absensi->jam_absen,
                    'keterangan' => $keterangan
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error proses absensi selfie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get riwayat absensi hari ini untuk guru
     */
    public function riwayat()
    {
        $guruId = Auth::user()->guru_id;
        $guru = Auth::user()->guru;

        // Check if AJAX request (return JSON)
        if (request()->wantsJson() || request()->ajax()) {
            $riwayat = Absensi::with(['jadwal.kelas', 'jadwal.mataPelajaran'])
                ->where('guru_id', $guruId)
                ->whereDate('tanggal', Carbon::today())
                ->orderBy('jam_absen', 'asc')
                ->get();

            return response()->json($riwayat->map(function($item) {
                return [
                    'id' => $item->id,
                    'jam_masuk' => $item->jam_absen ? Carbon::parse($item->jam_absen)->format('H:i') : '-',
                    'jam_absen' => $item->jam_absen ? Carbon::parse($item->jam_absen)->format('H:i') : '-',
                    'status_kehadiran' => $item->status_kehadiran,
                    'metode_absensi' => $item->metode_absensi,
                    'keterangan' => $item->keterangan,
                    'jadwal' => [
                        'id' => $item->jadwal->id ?? null,
                        'mata_pelajaran' => [
                            'nama_mapel' => $item->jadwal->mataPelajaran->nama_mapel ?? '-'
                        ],
                        'kelas' => [
                            'nama_kelas' => $item->jadwal->kelas->nama_kelas ?? '-'
                        ]
                    ]
                ];
            }));
        }

        // Otherwise return view
        $riwayat = Absensi::with(['jadwal.kelas', 'jadwal.mataPelajaran'])
            ->where('guru_id', $guruId)
            ->whereDate('tanggal', Carbon::today())
            ->orderBy('jam_absen', 'asc')
            ->get();

        return view('guru.absensi.riwayat', compact('riwayat', 'guru'));
    }

    /**
     * Calculate distance between two GPS coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    /**
     * Save selfie image
     */
    private function saveSelfie($base64Image, $guruId)
    {
        try {
            // Remove data:image/png;base64, prefix if exists
            $image = str_replace('data:image/png;base64,', '', $base64Image);
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            // Decode base64
            $imageData = base64_decode($image);

            // Generate filename
            $filename = 'selfie_' . $guruId . '_' . time() . '.jpg';
            $path = storage_path('app/public/selfie/' . $filename);

            // Create directory if not exists
            if (!file_exists(storage_path('app/public/selfie'))) {
                mkdir(storage_path('app/public/selfie'), 0755, true);
            }

            // Save file
            file_put_contents($path, $imageData);

            return 'selfie/' . $filename;

        } catch (\Exception $e) {
            \Log::error('Error saving selfie: ' . $e->getMessage());
            return null;
        }
    }
}
