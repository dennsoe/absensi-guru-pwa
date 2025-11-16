<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
    ];

    /**
     * Relationships
     */
    public function jadwalMengajar()
    {
        return $this->hasMany(JadwalMengajar::class, 'mapel_id');
    }
}
