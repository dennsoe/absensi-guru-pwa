<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPiket extends Model
{
    protected $table = 'guru_piket';

    protected $fillable = [
        'guru_id',
        'hari',
        'tahun_ajaran',
        'status',
    ];

    /**
     * Relationships
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query;
    }

    public function scopeHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    public function scopeTahunAjaran($query, $tahun)
    {
        return $query->where('tahun_ajaran', $tahun);
    }
}
