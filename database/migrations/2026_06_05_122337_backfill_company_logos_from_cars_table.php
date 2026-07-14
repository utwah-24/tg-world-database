<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Company::backfillLogosFromCars();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data backfill — no rollback.
    }
};
