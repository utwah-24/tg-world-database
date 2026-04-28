<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sold_cars', function (Blueprint $table) {
            $table->json('car_pics')->nullable()->after('car_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('car_pics')->nullable()->after('car_name');
        });
    }

    public function down(): void
    {
        Schema::table('sold_cars', function (Blueprint $table) {
            $table->dropColumn('car_pics');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('car_pics');
        });
    }
};
