<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('vehicle_model_id')
                ->nullable()
                ->after('brand')
                ->constrained('vehicle_models')
                ->nullOnDelete();

            $table->string('model_label', 255)->nullable()->after('vehicle_model_id');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['vehicle_model_id']);
            $table->dropColumn(['vehicle_model_id', 'model_label']);
        });
    }
};
