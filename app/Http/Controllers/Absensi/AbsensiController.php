<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Absensi, QrCode, JadwalMengajar, Guru};
use Illuminate\Support\Facades\{Auth, Storage};
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Intervention\Image\Laravel\Facades\Image;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Halaman Scan QR untuk Absensi
     */
    public function scanQr()
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        // Cek apakah sudah absen hari ini
        $sudahAbsen = Absensi::where('guru_id', $guru->id)
                             ->whereDate('tanggal', today())
                             ->exists();

        return view('absensi.scan-qr', compact('sudahAbsen'));
    }

    /**
     * Proses Absensi via QR Code
     */
    public function prosesAbsensiQr(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $guru = Auth::user()->guru;

        // Validasi QR Code
        $qrCode = QrCode::where('kode', $validated['qr_code'])
                        ->where('status', 'aktif')
                        ->where('waktu_kadaluarsa', '>', now())
                        ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        // Validasi GPS
        if (!$this->validateGPS($validated['latitude'], $validated['longitude'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi Anda di luar jangkauan sekolah.'
            ], 400);
        }

        // Cek sudah absen hari ini
        $sudahAbsen = Absensi::where('guru_id', $guru->id)
                             ->whereDate('tanggal', today())
                             ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini.'
            ], 400);
        }

        // Cek jadwal mengajar hari ini
        $jadwal = JadwalMengajar::where('guru_id', $guru->id)
                                ->where('hari', now()->locale('id')->dayName)
                                ->where('status', 'aktif')
                                ->orderBy('jam_mulai')
                                ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal mengajar hari ini.'
            ], 400);
        }

        // Hitung keterlambatan
        $waktuMasuk = now();
        $jamMulai = Carbon::parse($jadwal->jam_mulai);
        $toleransi = config('absensi.toleransi_terlambat', 15);

        $statusKeterlambatan = 'tepat_waktu';
        $menitTerlambat = 0;

        if ($waktuMasuk->gt($jamMulai->addMinutes($toleransi))) {
            $statusKeterlambatan = 'terlambat';
            $menitTerlambat = $waktuMasuk->diffInMinutes($jamMulai);
        }

        // Simpan absensi
        $absensi = Absensi::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $guru->id,
            'tanggal' => today(),
            'jam_masuk' => $waktuMasuk->format('H:i:s'),
            'status_kehadiran' => $statusKeterlambatan,
            'metode_absensi' => 'qr_code',
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat.',
            'data' => $absensi
        ]);
    }

    /**
     * Halaman Selfie untuk Absensi (alternatif QR)
     */
    public function selfie()
    {
        $guru = Auth::user()->guru;

        $sudahAbsen = Absensi::where('guru_id', $guru->id)
                             ->whereDate('tanggal', today())
                             ->exists();

        return view('absensi.selfie', compact('sudahAbsen'));
    }

    /**
     * Proses Absensi via Selfie
     */
    public function prosesAbsensiSelfie(Request $request)
    {
        $validated = $request->validate([
            'foto_selfie' => 'required|string', // base64
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $guru = Auth::user()->guru;

        // Validasi GPS
        if (!$this->validateGPS($validated['latitude'], $validated['longitude'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi Anda di luar jangkauan sekolah.'
            ], 400);
        }

        // Simpan foto selfie
        $fotoPath = $this->saveSelfie($validated['foto_selfie'], $guru->id);

        if (!$fotoPath) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan foto selfie.'
            ], 500);
        }

        // Cek jadwal
        $jadwal = JadwalMengajar::where('guru_id', $guru->id)
                                ->where('hari', now()->locale('id')->dayName)
                                ->where('status', 'aktif')
                                ->orderBy('jam_mulai')
                                ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal mengajar hari ini.'
            ], 400);
        }

        // Simpan absensi
        $absensi = Absensi::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $guru->id,
            'tanggal' => today(),
            'jam_masuk' => now()->format('H:i:s'),
            'status_kehadiran' => 'hadir',
            'metode_absensi' => 'selfie',
            'foto_selfie' => $fotoPath,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'validasi_ketua_kelas' => false, // Perlu validasi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi selfie berhasil. Menunggu validasi ketua kelas.',
            'data' => $absensi
        ]);
    }

    /**
     * Validasi GPS
     */
    private function validateGPS($lat, $lng)
    {
        $sekolahLat = config('gps.latitude');
        $sekolahLng = config('gps.longitude');
        $radius = config('gps.radius', 100); // meter

        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat - $sekolahLat);
        $dLng = deg2rad($lng - $sekolahLng);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($sekolahLat)) * cos(deg2rad($lat)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance <= $radius;
    }

    /**
     * Simpan foto selfie
     */
    private function saveSelfie($base64Image, $guruId)
    {
        try {
            // Decode base64
            $image = Image::read($base64Image);

            // Resize jika terlalu besar
            $image->scale(width: 800);

            // Generate nama file
            $filename = 'selfie_' . $guruId . '_' . now()->format('YmdHis') . '.jpg';
            $path = 'absensi/selfie/' . $filename;

            // Simpan ke storage
            Storage::disk('public')->put($path, $image->toJpeg(80));

            return $path;
        } catch (\Exception $e) {
            logger()->error('Error saving selfie: ' . $e->getMessage());
            return null;
        }
    }
}
