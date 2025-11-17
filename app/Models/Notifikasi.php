<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'kategori',
        'is_read',
        'read_at',
        'link_url',
        'icon',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }
}
