<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qr_codes';

    protected $fillable = [
        'guru_id',
        'jadwal_id',
        'qr_data',
        'qr_image_path',
        'expired_at',
        'is_used',
        'used_at',
        'used_by_ketua_kelas',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_id');
    }

    public function usedByKetuaKelas()
    {
        return $this->belongsTo(User::class, 'used_by_ketua_kelas');
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query->where('is_used', false)
                     ->where('expired_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expired_at', '<=', now());
    }
}
