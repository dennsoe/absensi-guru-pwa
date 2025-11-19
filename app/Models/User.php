<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'nama',
        'email',
        'nip',
        'no_hp',
        'foto_profil',
        'role',
        'guru_id',
        'kelas_id',
        'is_active',
        'status',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationships
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'user_id');
    }

    /**
     * Accessors
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto_profil) {
            // Cek apakah path sudah include 'foto-profil' atau belum
            $path = str_contains($this->foto_profil, 'foto-profil')
                ? $this->foto_profil
                : 'foto-profil/' . $this->foto_profil;

            if (file_exists(storage_path('app/public/' . $path))) {
                return asset('storage/' . $path);
            }
        }
        return asset('assets/images/avatars/default-avatar.svg');
    }

    public function getInisialAttribute()
    {
        $words = explode(' ', $this->nama);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->nama, 0, 2));
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query;
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
