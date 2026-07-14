<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('booked_at');
            $table->string('phone', 20)->nullable()->after('customer_name');
            $table->string('email')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'phone', 'email']);
        });
    }
};
