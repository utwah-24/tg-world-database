<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('normalized_username')->nullable()->unique();
            $table->string('normalized_email')->nullable()->unique();
            $table->string('normalized_phone', 20)->nullable()->unique();
            $table->string('role')->default('customer');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->rememberToken();
        });

        DB::table('users')->orderBy('id')->each(function ($user): void {
            $username = mb_strtolower(trim((string) $user->username));
            $email = $user->email === null ? null : mb_strtolower(trim((string) $user->email));
            $phone = preg_replace('/[^\d+]/', '', trim((string) $user->phone_number)) ?? '';
            if (str_starts_with($phone, '00')) {
                $phone = '+'.substr($phone, 2);
            }
            if (str_starts_with($phone, '0')) {
                $phone = '+255'.substr($phone, 1);
            }
            if ($phone !== '' && ! str_starts_with($phone, '+')) {
                $phone = '+'.$phone;
            }

            DB::table('users')->where('id', $user->id)->update([
                'normalized_username' => $username,
                'normalized_email' => $email,
                'normalized_phone' => $phone ?: null,
            ]);
        });

        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash', 64)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::dropIfExists('auth_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'normalized_username', 'normalized_email', 'normalized_phone', 'role',
                'email_verified_at', 'phone_verified_at', 'disabled_at', 'remember_token',
            ]);
        });
    }
};
