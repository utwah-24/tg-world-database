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
            $table->timestamp('sold_at')->nullable()->after('is_sold');
        });

        DB::table('cars')
            ->where('is_sold', 'sold')
            ->whereNull('sold_at')
            ->update(['sold_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('sold_at');
        });
    }
};
