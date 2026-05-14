<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sold_cars', function (Blueprint $table) {
            $table->unsignedInteger('total_available')->nullable()->after('price_sold')
                ->comment('Snapshot of cars.total_available at the time this sale was recorded');
        });
    }

    public function down(): void
    {
        Schema::table('sold_cars', function (Blueprint $table) {
            $table->dropColumn('total_available');
        });
    }
};
