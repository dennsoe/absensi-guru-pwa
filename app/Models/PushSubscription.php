<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';

    protected $fillable = [
        'user_id',
        'endpoint',
        'auth',
        'p256dh',
        'user_agent',
        'is_active',
        'last_used',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
