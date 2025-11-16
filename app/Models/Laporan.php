<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = [
        'judul',
        'tipe_laporan',
        'periode_mulai',
        'periode_selesai',
        'file_path',
        'format',
        'dibuat_oleh',
        'data_json',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'data_json' => 'array',
    ];

    /**
     * Relationships
     */
    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Scopes
     */
    public function scopeTipe($query, $tipe)
    {
        return $query->where('tipe_laporan', $tipe);
    }
}
