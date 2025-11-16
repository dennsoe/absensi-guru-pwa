<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPengganti extends Model
{
    protected $table = 'guru_pengganti';

    protected $fillable = [
        'jadwal_id',
        'guru_asli_id',
        'guru_pengganti_id',
        'tanggal',
        'alasan',
        'status',
        'approved_by',
        'approved_at',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_id');
    }

    public function guruAsli()
    {
        return $this->belongsTo(Guru::class, 'guru_asli_id');
    }

    public function guruPengganti()
    {
        return $this->belongsTo(Guru::class, 'guru_pengganti_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
