<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'wali_kelas_id',
        'ketua_kelas_user_id',
        'tahun_ajaran',
    ];

    /**
     * Relationships
     */
    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function ketuaKelas()
    {
        return $this->belongsTo(User::class, 'ketua_kelas_user_id');
    }

    public function jadwalMengajar()
    {
        return $this->hasMany(JadwalMengajar::class, 'kelas_id');
    }

    /**
     * Scopes
     */
    public function scopeTahunAjaran($query, $tahun)
    {
        return $query->where('tahun_ajaran', $tahun);
    }

    public function scopeTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }
}
