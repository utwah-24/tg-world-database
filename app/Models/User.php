<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'email',
        'password',
        'phone_number',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
