<?php

namespace App\Services;

use App\Models\{IzinCuti, Guru, Absensi, JadwalMengajar};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IzinCutiService
{
    /**
     * Create new izin/cuti request
     */
    public function createIzinCuti(array $data)
    {
        DB::beginTransaction();

        try {
            // Validate dates
            $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
            $tanggalSelesai = Carbon::parse($data['tanggal_selesai']);

            if ($tanggalSelesai->lt($tanggalMulai)) {
                throw new \Exception('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
            }

            // Check for overlapping izin
            $overlapping = IzinCuti::where('guru_id', $data['guru_id'])
                ->where('status', '!=', 'rejected')
                ->where(function($query) use ($tanggalMulai, $tanggalSelesai) {
                    $query->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                        ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                        ->orWhere(function($q) use ($tanggalMulai, $tanggalSelesai) {
                            $q->where('tanggal_mulai', '<=', $tanggalMulai)
                              ->where('tanggal_selesai', '>=', $tanggalSelesai);
                        });
                })
                ->exists();

            if ($overlapping) {
                throw new \Exception('Sudah ada izin/cuti di periode yang sama');
            }

            // Calculate duration
            $durasi = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

            // Handle file upload
            $filePath = null;
            if (isset($data['file_pendukung']) && $data['file_pendukung']) {
                $filePath = $data['file_pendukung']->store('izin-cuti', 'public');
            }

            // Generate nomor surat
            $nomorSurat = $this->generateNomorSurat($data['jenis']);

            // Create izin/cuti
            $izinCuti = IzinCuti::create([
                'guru_id' => $data['guru_id'],
                'jenis' => $data['jenis'],
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'durasi' => $durasi,
                'alasan' => $data['alasan'],
                'file_pendukung' => $filePath,
                'nomor_surat' => $nomorSurat,
                'status' => 'pending',
            ]);

            // Mark affected jadwal as needs substitute
            $this->markJadwalNeedsSubstitute($data['guru_id'], $tanggalMulai, $tanggalSelesai);

            DB::commit();

            return [
                'success' => true,
                'data' => $izinCuti,
                'message' => 'Permohonan izin/cuti berhasil diajukan'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve izin/cuti request
     */
    public function approveIzinCuti($id, $userId, $catatan = null)
    {
        DB::beginTransaction();

        try {
            $izinCuti = IzinCuti::findOrFail($id);

            if ($izinCuti->status !== 'pending') {
                throw new \Exception('Izin/cuti sudah diproses sebelumnya');
            }

            $izinCuti->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => Carbon::now(),
                'catatan_approval' => $catatan,
            ]);

            // Create automatic alpha records for the duration
            $this->createAlphaRecords($izinCuti);

            // TODO: Send notification to guru

            DB::commit();

            return [
                'success' => true,
                'message' => 'Izin/cuti berhasil disetujui'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reject izin/cuti request
     */
    public function rejectIzinCuti($id, $userId, $alasanPenolakan)
    {
        DB::beginTransaction();

        try {
            $izinCuti = IzinCuti::findOrFail($id);

            if ($izinCuti->status !== 'pending') {
                throw new \Exception('Izin/cuti sudah diproses sebelumnya');
            }

            $izinCuti->update([
                'status' => 'rejected',
                'approved_by' => $userId,
                'approved_at' => Carbon::now(),
                'catatan_approval' => $alasanPenolakan,
            ]);

            // Remove needs_substitute flag from jadwal
            $this->removeJadwalSubstituteFlag($izinCuti->guru_id, $izinCuti->tanggal_mulai, $izinCuti->tanggal_selesai);

            // TODO: Send notification to guru

            DB::commit();

            return [
                'success' => true,
                'message' => 'Izin/cuti ditolak'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel izin/cuti (by guru)
     */
    public function cancelIzinCuti($id, $guruId)
    {
        DB::beginTransaction();

        try {
            $izinCuti = IzinCuti::findOrFail($id);

            // Validate ownership
            if ($izinCuti->guru_id !== $guruId) {
                throw new \Exception('Anda tidak memiliki akses untuk membatalkan izin ini');
            }

            if ($izinCuti->status !== 'pending') {
                throw new \Exception('Hanya izin dengan status pending yang dapat dibatalkan');
            }

            $izinCuti->update([
                'status' => 'cancelled',
            ]);

            // Remove needs_substitute flag
            $this->removeJadwalSubstituteFlag($izinCuti->guru_id, $izinCuti->tanggal_mulai, $izinCuti->tanggal_selesai);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Permohonan izin/cuti berhasil dibatalkan'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get izin/cuti statistics for guru
     */
    public function getGuruStatistics($guruId, $year = null)
    {
        $year = $year ?? Carbon::now()->year;

        $statistics = [
            'total_izin' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('jenis', 'izin')
                ->where('status', 'approved')
                ->sum('durasi'),

            'total_sakit' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('jenis', 'sakit')
                ->where('status', 'approved')
                ->sum('durasi'),

            'total_cuti' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('jenis', 'cuti')
                ->where('status', 'approved')
                ->sum('durasi'),

            'pending' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('status', 'pending')
                ->count(),

            'approved' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('status', 'approved')
                ->count(),

            'rejected' => IzinCuti::where('guru_id', $guruId)
                ->whereYear('tanggal_mulai', $year)
                ->where('status', 'rejected')
                ->count(),
        ];

        $statistics['total_hari'] = $statistics['total_izin'] +
                                   $statistics['total_sakit'] +
                                   $statistics['total_cuti'];

        return $statistics;
    }

    /**
     * Get pending approval count
     */
    public function getPendingCount()
    {
        return IzinCuti::where('status', 'pending')->count();
    }

    /**
     * Generate nomor surat
     */
    private function generateNomorSurat($jenis)
    {
        $year = Carbon::now()->year;
        $count = IzinCuti::whereYear('created_at', $year)
            ->where('jenis', $jenis)
            ->count() + 1;

        $jenisCode = [
            'izin' => 'IZ',
            'sakit' => 'SK',
            'cuti' => 'CT'
        ];

        return sprintf('%s/%03d/%s/SDN', $jenisCode[$jenis] ?? 'IZ', $count, $year);
    }

    /**
     * Mark jadwal as needs substitute
     */
    private function markJadwalNeedsSubstitute($guruId, $tanggalMulai, $tanggalSelesai)
    {
        $period = Carbon::parse($tanggalMulai)->toPeriod($tanggalSelesai);

        foreach ($period as $date) {
            $hari = ucfirst($date->locale('id')->dayName);

            JadwalMengajar::where('guru_id', $guruId)
                ->where('hari', $hari)
                ->update(['needs_substitute' => true]);
        }
    }

    /**
     * Remove substitute flag from jadwal
     */
    private function removeJadwalSubstituteFlag($guruId, $tanggalMulai, $tanggalSelesai)
    {
        $period = Carbon::parse($tanggalMulai)->toPeriod($tanggalSelesai);

        foreach ($period as $date) {
            $hari = ucfirst($date->locale('id')->dayName);

            JadwalMengajar::where('guru_id', $guruId)
                ->where('hari', $hari)
                ->update(['needs_substitute' => false]);
        }
    }

    /**
     * Create alpha records for approved izin/cuti
     */
    private function createAlphaRecords($izinCuti)
    {
        $period = Carbon::parse($izinCuti->tanggal_mulai)
            ->toPeriod($izinCuti->tanggal_selesai);

        foreach ($period as $date) {
            $hari = ucfirst($date->locale('id')->dayName);

            // Get jadwal for this day
            $jadwalList = JadwalMengajar::where('guru_id', $izinCuti->guru_id)
                ->where('hari', $hari)
                
                ->get();

            foreach ($jadwalList as $jadwal) {
                // Check if absensi already exists
                $exists = Absensi::where('guru_id', $izinCuti->guru_id)
                    ->where('jadwal_id', $jadwal->id)
                    ->whereDate('tanggal', $date)
                    ->exists();

                if (!$exists) {
                    Absensi::create([
                        'guru_id' => $izinCuti->guru_id,
                        'jadwal_id' => $jadwal->id,
                        'tanggal' => $date,
                        'status_kehadiran' => $izinCuti->jenis, // izin/sakit/cuti
                        'keterangan' => "Auto created from izin/cuti: {$izinCuti->nomor_surat}",
                        'metode_absensi' => 'system',
                    ]);
                }
            }
        }
    }
}
