<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The admin form allows any vehicle type (e.g. "Crossover SUV"). MySQL ENUM
     * only allowed a fixed list (SQLSTATE 1265). VARCHAR matches phpMyAdmin / cPanel
     * manual changes and Filament free-text input.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `cars` MODIFY COLUMN `type` VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `cars` MODIFY COLUMN `type` ENUM('suv', 'truck', 'third_party', 'sedan', 'van', 'pickup') NULL");
        }
    }
};
