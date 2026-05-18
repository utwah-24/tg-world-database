<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cars')) {
            return;
        }

        Schema::table('cars', function (Blueprint $table) {
            if (! Schema::hasColumn('cars', 'is_coming_soon')) {
                $table->string('is_coming_soon', 10)->nullable();
            }

            if (! Schema::hasColumn('cars', 'arrival_date')) {
                $table->date('arrival_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cars')) {
            return;
        }

        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'arrival_date')) {
                $table->dropColumn('arrival_date');
            }

            if (Schema::hasColumn('cars', 'is_coming_soon')) {
                $table->dropColumn('is_coming_soon');
            }
        });
    }
};
