<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthSession extends Model
{
    protected $fillable = [
        'token_hash', 'user_id', 'expires_at', 'last_used_at', 'revoked_at',
        'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Client::class, 'user_id');
    }
}
