<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sold_cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id')->nullable()->comment('References cars.car_id');
            $table->string('car_name', 255);
            $table->timestamp('sold_at')->nullable();
            $table->string('price_sold', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sold_cars');
    }
};
