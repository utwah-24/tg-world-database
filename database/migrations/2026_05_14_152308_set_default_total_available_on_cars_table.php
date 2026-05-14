<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill all cars that have no stock count set
        DB::table('cars')->whereNull('total_available')->update(['total_available' => 1]);

        // Make the column non-nullable with a default of 1 going forward
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedInteger('total_available')->default(1)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedInteger('total_available')->nullable()->default(null)->change();
        });
    }
};
