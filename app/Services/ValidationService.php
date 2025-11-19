<?php

namespace App\Services;

use App\Models\{Guru, JadwalMengajar, Absensi, IzinCuti};
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Validate jadwal overlap for a guru
     */
    public function validateJadwalOverlap($guruId, $hari, $jamMulai, $jamSelesai, $excludeId = null)
    {
        $jamMulai = Carbon::parse($jamMulai);
        $jamSelesai = Carbon::parse($jamSelesai);

        $query = JadwalMengajar::where('guru_id', $guruId)
            ->where('hari', $hari)
            
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->where(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->whereTime('jam_mulai', '<=', $jamMulai)
                       ->whereTime('jam_selesai', '>', $jamMulai);
                })
                ->orWhere(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->whereTime('jam_mulai', '<', $jamSelesai)
                       ->whereTime('jam_selesai', '>=', $jamSelesai);
                })
                ->orWhere(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->whereTime('jam_mulai', '>=', $jamMulai)
                       ->whereTime('jam_selesai', '<=', $jamSelesai);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $overlapping = $query->first();

        if ($overlapping) {
            return [
                'valid' => false,
                'message' => "Jadwal bertabrakan dengan jadwal {$overlapping->mataPelajaran->nama_mapel} di kelas {$overlapping->kelas->nama_kelas} pada jam {$overlapping->jam_mulai} - {$overlapping->jam_selesai}",
                'conflicting_jadwal' => $overlapping,
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate if guru can take leave (check quota)
     */
    public function validateIzinQuota($guruId, $jenis, $durasi, $tahun = null)
    {
        $tahun = $tahun ?? Carbon::now()->year;

        $quotas = [
            'izin' => 12,    // 12 days per year
            'sakit' => null, // unlimited with certificate
            'cuti' => 12,    // 12 days per year
        ];

        if (!isset($quotas[$jenis])) {
            return [
                'valid' => false,
                'message' => 'Jenis izin/cuti tidak valid',
            ];
        }

        if ($quotas[$jenis] === null) {
            return ['valid' => true]; // Unlimited
        }

        $used = IzinCuti::where('guru_id', $guruId)
            ->where('jenis', $jenis)
            ->where('status', 'approved')
            ->whereYear('tanggal_mulai', $tahun)
            ->sum('durasi');

        $remaining = $quotas[$jenis] - $used;

        if ($durasi > $remaining) {
            return [
                'valid' => false,
                'message' => "Kuota {$jenis} tidak mencukupi. Tersisa: {$remaining} hari, diminta: {$durasi} hari",
                'quota' => $quotas[$jenis],
                'used' => $used,
                'remaining' => $remaining,
            ];
        }

        return [
            'valid' => true,
            'quota' => $quotas[$jenis],
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    /**
     * Validate if guru can submit izin for date range
     */
    public function validateIzinDateRange($guruId, $tanggalMulai, $tanggalSelesai, $excludeId = null)
    {
        $tanggalMulai = Carbon::parse($tanggalMulai);
        $tanggalSelesai = Carbon::parse($tanggalSelesai);

        // Check if dates are valid
        if ($tanggalMulai->gt($tanggalSelesai)) {
            return [
                'valid' => false,
                'message' => 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai',
            ];
        }

        // Check for overlapping izin/cuti
        $query = IzinCuti::where('guru_id', $guruId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                  ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                  ->orWhere(function($q2) use ($tanggalMulai, $tanggalSelesai) {
                      $q2->where('tanggal_mulai', '<=', $tanggalMulai)
                         ->where('tanggal_selesai', '>=', $tanggalSelesai);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $overlapping = $query->first();

        if ($overlapping) {
            return [
                'valid' => false,
                'message' => "Sudah ada pengajuan {$overlapping->jenis} pada tanggal {$overlapping->tanggal_mulai->format('d/m/Y')} - {$overlapping->tanggal_selesai->format('d/m/Y')} dengan status {$overlapping->status}",
                'conflicting_izin' => $overlapping,
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate absensi submission
     */
    public function validateAbsensiSubmission($guruId, $jadwalId, $tanggal)
    {
        $tanggal = Carbon::parse($tanggal);

        // Check if jadwal exists and is active
        $jadwal = JadwalMengajar::find($jadwalId);
        if (!$jadwal || $jadwal->status !== 'aktif') {
            return [
                'valid' => false,
                'message' => 'Jadwal tidak ditemukan atau tidak aktif',
            ];
        }

        // Check if guru matches
        if ($jadwal->guru_id != $guruId) {
            return [
                'valid' => false,
                'message' => 'Jadwal tidak sesuai dengan guru',
            ];
        }

        // Check if already submitted
        $existing = Absensi::where('guru_id', $guruId)
            ->where('jadwal_id', $jadwalId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return [
                'valid' => false,
                'message' => 'Absensi untuk jadwal ini sudah pernah disubmit',
                'existing_absensi' => $existing,
            ];
        }

        // Check if date matches jadwal hari
        $hari = ucfirst($tanggal->locale('id')->dayName);
        if ($jadwal->hari !== $hari) {
            return [
                'valid' => false,
                'message' => "Jadwal ini untuk hari {$jadwal->hari}, bukan {$hari}",
            ];
        }

        return ['valid' => true, 'jadwal' => $jadwal];
    }

    /**
     * Validate if time is within schedule
     */
    public function validateAbsensiTime($jadwal, $waktuAbsen)
    {
        $waktuAbsen = Carbon::parse($waktuAbsen);
        $jamMulai = Carbon::parse($jadwal->jam_mulai);
        $jamSelesai = Carbon::parse($jadwal->jam_selesai);

        // Allow check-in from 30 minutes before until schedule ends
        $earliestCheckIn = $jamMulai->copy()->subMinutes(30);

        if ($waktuAbsen->lt($earliestCheckIn)) {
            return [
                'valid' => false,
                'message' => 'Terlalu awal untuk absen. Absen dapat dilakukan 30 menit sebelum jadwal dimulai',
            ];
        }

        if ($waktuAbsen->gt($jamSelesai)) {
            return [
                'valid' => false,
                'message' => 'Jadwal sudah berakhir. Tidak dapat melakukan absensi',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate GPS coordinates
     */
    public function validateGPSCoordinates($lat, $lng, $allowedRadius = 100)
    {
        $validator = Validator::make([
            'latitude' => $lat,
            'longitude' => $lng,
        ], [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => 'Koordinat GPS tidak valid',
                'errors' => $validator->errors(),
            ];
        }

        // Get school coordinates from config
        $schoolLat = config('gps.school_latitude');
        $schoolLng = config('gps.school_longitude');

        if (!$schoolLat || !$schoolLng) {
            return ['valid' => true]; // Skip validation if not configured
        }

        $distance = $this->calculateDistance($lat, $lng, $schoolLat, $schoolLng);

        if ($distance > $allowedRadius) {
            return [
                'valid' => false,
                'message' => "Lokasi Anda terlalu jauh dari sekolah ({$distance}m). Maksimal: {$allowedRadius}m",
                'distance' => $distance,
            ];
        }

        return ['valid' => true, 'distance' => $distance];
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($earthRadius * $c);
    }

    /**
     * Validate photo upload
     */
    public function validatePhotoUpload($file)
    {
        $validator = Validator::make([
            'photo' => $file,
        ], [
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => 'File foto tidak valid',
                'errors' => $validator->errors(),
            ];
        }

        return ['valid' => true];
    }
}
