<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('car_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('proposed_price');
            $table->char('currency', 3)->default('TZS');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city', 100)->nullable();
            $table->string('delivery_region', 100)->nullable();
            $table->string('delivery_postal_code', 30)->nullable();
            $table->text('customer_notes')->nullable();
            $table->json('vehicle_snapshot');
            $table->text('staff_notes')->nullable();
            $table->unsignedBigInteger('counter_price')->nullable();
            $table->string('preview_pdf_path', 500)->nullable();
            $table->char('submission_fingerprint', 64)->unique();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->foreign('car_id')->references('car_id')->on('cars')->nullOnDelete();
            $table->index('customer_id');
            $table->index('car_id');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('quotation_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['quotation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_audits');
        Schema::dropIfExists('quotations');
    }
};
