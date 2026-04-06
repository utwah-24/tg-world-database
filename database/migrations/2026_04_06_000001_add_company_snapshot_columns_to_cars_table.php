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
            $table->string('company_label', 255)->nullable()->after('company_id');
            $table->string('company_logo_path')->nullable()->after('company_label');
        });

        // Backfill from companies for existing rows
        DB::statement('
            UPDATE cars c
            INNER JOIN companies co ON co.id = c.company_id
            SET c.company_label = co.name, c.company_logo_path = co.logo
            WHERE c.company_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['company_label', 'company_logo_path']);
        });
    }
};
