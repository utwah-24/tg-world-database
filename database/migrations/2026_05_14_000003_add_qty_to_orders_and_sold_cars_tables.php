<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('qty')->default(1)->after('total_available')
                ->comment('Number of units bought in this order');
        });

        Schema::table('sold_cars', function (Blueprint $table) {
            $table->unsignedSmallInteger('qty')->default(1)->after('total_available')
                ->comment('Number of units in this sale');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qty');
        });

        Schema::table('sold_cars', function (Blueprint $table) {
            $table->dropColumn('qty');
        });
    }
};
