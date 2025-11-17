<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'jadwal_id',
        'guru_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status_kehadiran',
        'metode_absensi',
        'foto_selfie',
        'qr_code_data',
        'latitude',
        'longitude',
        'validasi_gps',
        'jarak_dari_sekolah',
        'keterangan',
        'file_pendukung',
        'validasi_ketua_kelas',
        'ketua_kelas_user_id',
        'waktu_validasi_ketua',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
        'validasi_gps' => 'boolean',
        'validasi_ketua_kelas' => 'boolean',
        'waktu_validasi_ketua' => 'datetime',
        'approved_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Relationships
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function ketuaKelas()
    {
        return $this->belongsTo(User::class, 'ketua_kelas_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeHadir($query)
    {
        return $query->where('status_kehadiran', 'hadir');
    }

    public function scopeAlpha($query)
    {
        return $query->where('status_kehadiran', 'alpha');
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)
                     ->whereYear('tanggal', $tahun);
    }
}
