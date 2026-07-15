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
            $table->boolean('promo_set')->default(false)->after('location')
                ->comment('True when promotion toggle is on for this car');
        });

        // Backfill from existing pivot links
        if (Schema::hasTable('car_promotion')) {
            $carIds = DB::table('car_promotion')->distinct()->pluck('car_id');
            if ($carIds->isNotEmpty()) {
                DB::table('cars')->whereIn('car_id', $carIds)->update(['promo_set' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('promo_set');
        });
    }
};
