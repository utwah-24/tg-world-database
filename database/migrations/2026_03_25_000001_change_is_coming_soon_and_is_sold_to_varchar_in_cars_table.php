<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change column types first (tinyint -> varchar)
        Schema::table('cars', function (Blueprint $table) {
            $table->string('is_coming_soon', 10)->nullable()->change();
            $table->string('is_sold', 20)->nullable()->change();
        });

        // Migrate existing boolean data to string values
        DB::table('cars')->where('is_coming_soon', '1')->update(['is_coming_soon' => 'set']);
        DB::table('cars')->where('is_coming_soon', '0')->update(['is_coming_soon' => null]);

        DB::table('cars')->where('is_sold', '1')->update(['is_sold' => 'sold']);
        DB::table('cars')->where('is_sold', '0')->orWhereNull('is_sold')->update(['is_sold' => 'available']);
    }

    public function down(): void
    {
        // Reverse data before changing column type back
        DB::table('cars')->where('is_coming_soon', 'set')->update(['is_coming_soon' => '1']);
        DB::table('cars')->whereNull('is_coming_soon')->update(['is_coming_soon' => '0']);

        DB::table('cars')->where('is_sold', 'sold')->update(['is_sold' => '1']);
        DB::table('cars')->where('is_sold', 'available')->update(['is_sold' => '0']);

        Schema::table('cars', function (Blueprint $table) {
            $table->boolean('is_coming_soon')->default(false)->change();
            $table->boolean('is_sold')->default(false)->change();
        });
    }
};
