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
        return $this->belongsTo(User::class);
    }

    public function jadwalMengajar()
    {
        return $this->hasMany(JadwalMengajar::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function guruPiket()
    {
        return $this->hasMany(GuruPiket::class);
    }

    public function izinCuti()
    {
        return $this->hasMany(IzinCuti::class);
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class);
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
