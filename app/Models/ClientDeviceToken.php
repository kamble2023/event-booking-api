<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDeviceToken extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'token',
        'device_type',
        'status',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
