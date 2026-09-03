<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('name')->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('username')->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('users', 'phone_number')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone_number', 20)->nullable();
            });
        }

        $seen = DB::table('users')
            ->whereNotNull('normalized_username')
            ->pluck('normalized_username')
            ->filter()
            ->mapWithKeys(fn (string $value): array => [$value => true])
            ->all();

        DB::table('users')
            ->whereNull('username')
            ->orderBy('id')
            ->each(function ($user) use (&$seen): void {
                $base = mb_strtolower(trim((string) ($user->name ?: 'user-'.$user->id)));
                $base = preg_replace('/[^a-z0-9_.-]+/', '-', $base) ?: 'user-'.$user->id;
                $base = trim($base, '-.');
                $base = mb_substr($base ?: 'user-'.$user->id, 0, 24);
                $normalized = $base;
                $suffix = 1;

                while (isset($seen[$normalized])) {
                    $normalized = mb_substr($base, 0, 24).'-'.$suffix++;
                }
                $seen[$normalized] = true;

                DB::table('users')->where('id', $user->id)->update([
                    'username' => $normalized,
                    'normalized_username' => $normalized,
                    'normalized_email' => $user->email === null ? null : mb_strtolower(trim((string) $user->email)),
                ]);
            });
    }

    public function down(): void
    {
        // This migration repairs divergent production history. Removing these
        // columns could destroy customer identity data, so rollback is a no-op.
    }
};
