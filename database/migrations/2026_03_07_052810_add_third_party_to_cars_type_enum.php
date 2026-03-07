<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires re-declaring all ENUM values to add a new one
        DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck', 'third_party') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cars MODIFY COLUMN type ENUM('suv', 'truck') NULL");
    }
};
