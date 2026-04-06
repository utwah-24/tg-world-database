<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Upsert all companies ────────────────────────────────────────────
        $companies = [
            'Scania',
            'Toyota',
            'Subaru',
            'Ford',
            'Range Rover',
            'Land Rover',
            'BMW',
            'Suzuki',
            'Sinotruck',
        ];

        foreach ($companies as $name) {
            DB::table('companies')->updateOrInsert(
                ['name' => $name],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Helper: resolve company id by name
        $id = fn (string $name): int => DB::table('companies')->where('name', $name)->value('id');

        // ── 2. Map each car_id → company name ─────────────────────────────────
        $carCompanyMap = [
            // Scania
            1 => 'Scania',
            2 => 'Scania',

            // Toyota
            3 => 'Toyota',
            4 => 'Toyota',
            7 => 'Toyota',
            9 => 'Toyota',
            10 => 'Toyota',
            12 => 'Toyota',
            13 => 'Toyota',
            14 => 'Toyota',
            15 => 'Toyota',
            18 => 'Toyota',
            20 => 'Toyota',
            22 => 'Toyota',
            23 => 'Toyota',
            24 => 'Toyota',
            26 => 'Toyota',
            30 => 'Toyota',
            31 => 'Toyota',

            // Subaru
            5 => 'Subaru',

            // Ford
            6 => 'Ford',
            25 => 'Ford',

            // Range Rover
            8 => 'Range Rover',
            17 => 'Range Rover',

            // Land Rover
            11 => 'Land Rover',

            // BMW
            16 => 'BMW',

            // Suzuki
            19 => 'Suzuki',

            // Sinotruck
            27 => 'Sinotruck',
            28 => 'Sinotruck',
        ];

        // ── 3. Update each car's company_id ───────────────────────────────────
        foreach ($carCompanyMap as $carId => $companyName) {
            DB::table('cars')
                ->where('car_id', $carId)
                ->update(['company_id' => $id($companyName)]);
        }

        $this->command->info('Companies seeded and linked to all cars.');
    }
}
