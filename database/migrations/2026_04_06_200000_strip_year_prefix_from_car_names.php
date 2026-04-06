<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique(['car_name']);
        });

        $stripLeadingYear = static function (string $name): string {
            return trim(preg_replace('/^(19[89]\d|20[0-4]\d)\s+/u', '', $name) ?? $name);
        };

        foreach (DB::table('cars')->orderBy('car_id')->cursor() as $car) {
            $newName = $stripLeadingYear((string) $car->car_name);

            if ($newName !== '' && $newName !== $car->car_name) {
                DB::table('cars')->where('car_id', $car->car_id)->update(['car_name' => $newName]);
            }
        }

        // Same (car_name, year) after strip → disambiguate with stable suffix
        $dupGroups = DB::table('cars')
            ->select('car_name', 'year')
            ->selectRaw('COUNT(*) as c')
            ->groupBy('car_name', 'year')
            ->having('c', '>', 1)
            ->get();

        foreach ($dupGroups as $g) {
            $rows = DB::table('cars')
                ->where('car_name', $g->car_name)
                ->where('year', $g->year)
                ->orderBy('car_id')
                ->get();

            foreach ($rows->skip(1) as $row) {
                DB::table('cars')->where('car_id', $row->car_id)->update([
                    'car_name' => $row->car_name.' · #'.$row->car_id,
                ]);
            }
        }

        Schema::table('cars', function (Blueprint $table) {
            $table->unique(['car_name', 'year'], 'cars_car_name_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique('cars_car_name_year_unique');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->unique('car_name');
        });
    }
};
