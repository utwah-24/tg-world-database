<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use BINARY comparison to force case-sensitive match and update
        DB::statement("UPDATE companies SET name = 'Scania' WHERE BINARY name = 'scania'");
    }

    public function down(): void {}
};
