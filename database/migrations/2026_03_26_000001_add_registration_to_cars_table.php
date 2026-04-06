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
            $table->string('registration', 20)->default('unregistered')->after('is_sold');
        });

        // Default all existing cars to 'unregistered'
        DB::table('cars')->update(['registration' => 'unregistered']);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('registration');
        });
    }
};
