<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tgworld.com'],
            [
                'username'     => 'Admin',
                'password'     => 'password', // will be hashed by the User model cast
                'phone_number' => '0000000000',
            ]
        );
    }
}


