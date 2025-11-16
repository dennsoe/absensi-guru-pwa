<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';
    protected $primaryKey = 'qr_id';

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
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    // Relasi
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'guru_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_id', 'jadwal_id');
    }

    public function ketuaKelas()
    {
        return $this->belongsTo(User::class, 'used_by_ketua_kelas', 'user_id');
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expired_at', '>', now());
    }

    // Helper methods
    public function isExpired()
    {
        return now()->greaterThan($this->expired_at);
    }

    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function markAsUsed($ketuaKelasId)
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_by_ketua_kelas' => $ketuaKelasId,
        ]);
    }

    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public static function createQrCode($guruId, $jadwalId, $expiryMinutes = 5)
    {
        $token = self::generateToken();
        $data = json_encode([
            'token' => $token,
            'guru_id' => $guruId,
            'jadwal_id' => $jadwalId,
            'timestamp' => time(),
        ]);

        $signature = hash_hmac('sha256', $data, config('app.key'));
        $qrData = base64_encode($data . '|' . $signature);

        return self::create([
            'guru_id' => $guruId,
            'jadwal_id' => $jadwalId,
            'qr_data' => $qrData,
            'expired_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    public function validateSignature()
    {
        $parts = explode('|', base64_decode($this->qr_data));
        
        if (count($parts) !== 2) {
            return false;
        }

        [$data, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $data, config('app.key'));

        return hash_equals($expectedSignature, $signature);
    }
}
