<?php

namespace Database\Seeders;

use App\Models\Logo;
use Illuminate\Database\Seeder;

class LogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Logo::updateOrCreate(
            ['name' => 'logo-dark'],
            ['path' => 'logo-dark.jpeg'],
        );

        Logo::updateOrCreate(
            ['name' => 'logo-light'],
            ['path' => 'logo-light.jpeg'],
        );
    }
}
