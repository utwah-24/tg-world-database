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
            $table->boolean('in_dar')->default(true)->after('registration_number')
                ->comment('True = car is in Dar es Salaam; false = see location field');
            $table->string('location')->nullable()->after('in_dar')
                ->comment('Manual location when in_dar is false');
        });

        // All existing cars default to in Dar
        DB::table('cars')->update(['in_dar' => true]);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['in_dar', 'location']);
        });
    }
};
