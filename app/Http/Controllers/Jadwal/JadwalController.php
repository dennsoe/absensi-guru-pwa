<?php

namespace App\Http\Controllers\Jadwal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{JadwalMengajar, QrCode, Guru, Kelas};
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Illuminate\Support\Str;

class JadwalController extends Controller
{
    /**
     * Lihat Jadwal Hari Ini
     */
    public function hariIni()
    {
        $hari = ucfirst(now()->locale('id')->dayName);

        $jadwal = JadwalMengajar::where('hari', $hari)
                                
                                ->with(['guru', 'kelas', 'mataPelajaran'])
                                ->orderBy('jam_mulai')
                                ->get();

        return view('jadwal.hari-ini', compact('jadwal', 'hari'));
    }

    /**
     * Lihat Jadwal Per Kelas
     */
    public function perKelas(Request $request)
    {
        $kelas = Kelas::all();
        $kelasId = $request->get('kelas_id');

        $jadwal = collect();
        if ($kelasId) {
            $jadwal = JadwalMengajar::where('kelas_id', $kelasId)
                                    
                                    ->with(['guru', 'mataPelajaran'])
                                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')")
                                    ->orderBy('jam_mulai')
                                    ->get();
        }

        return view('jadwal.per-kelas', compact('kelas', 'jadwal', 'kelasId'));
    }

    /**
     * Lihat Jadwal Per Guru
     */
    public function perGuru(Request $request)
    {
        $guru = Guru::whereHas('user', function($q) {
            $q;
        })->get();
        $guruId = $request->get('guru_id');

        $jadwal = collect();
        if ($guruId) {
            $jadwal = JadwalMengajar::where('guru_id', $guruId)
                                    
                                    ->with(['kelas', 'mataPelajaran'])
                                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')")
                                    ->orderBy('jam_mulai')
                                    ->get();
        }

        return view('jadwal.per-guru', compact('guru', 'jadwal', 'guruId'));
    }

    /**
     * Generate QR Code untuk Jadwal (Guru Piket)
     */
    public function generateQrCode(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwal_mengajar,id',
        ]);

        $jadwal = JadwalMengajar::findOrFail($validated['jadwal_id']);

        // Generate kode unik
        $kode = Str::uuid()->toString();

        // Waktu kadaluarsa (default 10 menit)
        $expiry = config('absensi.qr_expiry_minutes', 10);
        $waktuKadaluarsa = now()->addMinutes($expiry);

        // Simpan QR Code
        $qrCode = QrCode::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $jadwal->guru_id,
            'kode' => $kode,
            'waktu_dibuat' => now(),
            'waktu_kadaluarsa' => $waktuKadaluarsa,
            'status' => 'aktif',
            'dibuat_oleh' => auth()->id(),
        ]);

        // Generate gambar QR Code
        $qrImage = QrCodeGenerator::format('svg')
                                  ->size(300)
                                  ->generate($kode);

        return response()->json([
            'success' => true,
            'qr_code' => [
                'id' => $qrCode->id,
                'kode' => $kode,
                'image' => base64_encode($qrImage),
                'expiry' => $waktuKadaluarsa->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Nonaktifkan QR Code
     */
    public function nonaktifkanQrCode(QrCode $qrCode)
    {
        $qrCode->update(['status' => 'nonaktif']);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil dinonaktifkan.'
        ]);
    }
}
