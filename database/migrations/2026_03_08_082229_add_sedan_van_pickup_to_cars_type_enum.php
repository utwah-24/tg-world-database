<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck', 'third_party', 'sedan', 'van', 'pickup') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck', 'third_party') NULL");
        }
    }
};
