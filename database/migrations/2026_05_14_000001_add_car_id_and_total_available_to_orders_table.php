<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('car_id')->nullable()->after('car_name')
                ->comment('References cars.car_id');
            $table->unsignedInteger('total_available')->nullable()->after('car_id')
                ->comment('Snapshot of cars.total_available after this order was placed');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['car_id', 'total_available']);
        });
    }
};
