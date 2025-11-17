<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'email',
        'no_hp',
        'alamat',
        'foto',
        'jenis_kelamin',
        'tanggal_lahir',
        'status_kepegawaian',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jadwalMengajar()
    {
        return $this->hasMany(JadwalMengajar::class, 'guru_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'guru_id');
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class, 'guru_id');
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function guruPiket()
    {
        return $this->hasMany(GuruPiket::class, 'guru_id');
    }

    public function izinCuti()
    {
        return $this->hasMany(IzinCuti::class, 'guru_id');
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'guru_id');
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('status', 'aktif');
        });
    }

    public function scopePns($query)
    {
        return $query->where('status_kepegawaian', 'PNS');
    }
}
