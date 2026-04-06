<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cars = DB::table('cars')->get(['car_id', 'car_name', 'year']);

        foreach ($cars as $car) {
            $name = $car->car_name;
            $year = $car->year;

            // Extract a 4-digit year (1980–2030) from anywhere in the name
            preg_match('/\b(19[89]\d|20[0-3]\d)\b/', $name, $matches);
            $yearInName = $matches[1] ?? null;

            $updates = [];

            // ── Case 1: year field is null → extract from name
            if ($year === null && $yearInName !== null) {
                $updates['year'] = (int) $yearInName;
            }

            // ── Case 2: year field is set but not present in the name → prepend it
            if ($year !== null && $yearInName === null) {
                $updates['car_name'] = $year.' '.$name;
            }

            // ── Case 3: year at end of name (e.g. "TOYOTA KLUGER 2003") → move to front
            if ($yearInName !== null && ! str_starts_with(trim($name), $yearInName)) {
                $cleaned = trim(preg_replace('/\b'.preg_quote($yearInName, '/').'\b/', '', $name));
                $updates['car_name'] = $yearInName.' '.$cleaned;
                if ($year === null) {
                    $updates['year'] = (int) $yearInName;
                }
            }

            if (! empty($updates)) {
                DB::table('cars')->where('car_id', $car->car_id)->update($updates);
            }
        }
    }

    public function down(): void {}
};
