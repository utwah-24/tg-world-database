<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->foreign('car_id')
                  ->references('car_id')
                  ->on('cars')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->dropForeign(['car_id']);
        });
    }
};
