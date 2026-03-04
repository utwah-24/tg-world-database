<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, HasName
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

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all users to access the Filament admin panel for now.
        // You can add role checks here later if needed.
        return true;
    }

    public function getFilamentName(): string
    {
        // Use the username field as the display name in Filament, with sensible fallbacks.
        if (! empty($this->username)) {
            return (string) $this->username;
        }

        if (! empty($this->email)) {
            return (string) $this->email;
        }

        return 'User';
    }
}
