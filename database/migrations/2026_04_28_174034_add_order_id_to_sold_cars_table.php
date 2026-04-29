<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sold_cars', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('sold_cars', function (Blueprint $table): void {
            $table->dropColumn('order_id');
        });
    }
};
