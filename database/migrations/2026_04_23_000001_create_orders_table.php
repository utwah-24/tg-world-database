<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->date('order_date');
            $table->string('car_name', 255);
            $table->string('invoice', 500)->nullable()->comment('Path to invoice PDF');
            $table->string('receipt', 500)->nullable()->comment('Path to receipt PDF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
