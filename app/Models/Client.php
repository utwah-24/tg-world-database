<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'normalized_username',
        'email',
        'normalized_email',
        'password',
        'phone_number',
        'normalized_phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class, 'user_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'customer_id');
    }
}
