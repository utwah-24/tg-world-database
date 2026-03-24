<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite uses TEXT for all columns — ENUM modification is MySQL-only
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck', 'third_party') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck') NULL");
        }
    }
};
