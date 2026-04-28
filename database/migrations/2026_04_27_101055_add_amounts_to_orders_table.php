<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->nullable()->after('receipt');
            $table->decimal('amount_due', 15, 2)->nullable()->after('amount_paid');
            $table->decimal('total_amount', 15, 2)->nullable()->after('amount_due');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'amount_due', 'total_amount']);
        });
    }
};
