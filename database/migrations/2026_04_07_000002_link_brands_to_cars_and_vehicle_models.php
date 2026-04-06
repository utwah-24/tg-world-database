<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Seed brands from existing cars.brand ─────────────────────────────
        $names = DB::table('cars')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        foreach ($names as $name) {
            DB::table('brands')->insertOrIgnore([
                'name' => trim($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Cars: add FK + snapshot, link rows, drop legacy column ───────────
        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('type')
                ->constrained('brands')
                ->nullOnDelete();

            $table->string('brand_label', 255)->nullable()->after('brand_id');
        });

        foreach (DB::table('cars')->cursor() as $car) {
            $raw = trim((string) ($car->brand ?? ''));
            if ($raw === '') {
                continue;
            }

            $brandId = DB::table('brands')
                ->whereRaw('LOWER(name) = ?', [strtolower($raw)])
                ->value('id');

            DB::table('cars')->where('car_id', $car->car_id)->update([
                'brand_id' => $brandId,
                'brand_label' => $raw,
            ]);
        }

        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('brand');
        });

        // ── vehicle_models: belong to a brand ────────────────────────────────
        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('id')
                ->constrained('brands')
                ->nullOnDelete();
        });

        foreach (DB::table('cars')->whereNotNull('vehicle_model_id')->cursor() as $car) {
            if (! $car->brand_id) {
                continue;
            }

            DB::table('vehicle_models')
                ->where('id', $car->vehicle_model_id)
                ->update(['brand_id' => $car->brand_id]);
        }

        $uncatId = DB::table('brands')->insertGetId([
            'name' => 'Uncategorized',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('vehicle_models')
            ->whereNull('brand_id')
            ->update(['brand_id' => $uncatId]);

        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->unique(['brand_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->dropUnique(['brand_id', 'name']);
        });

        Schema::table('vehicle_models', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
            $table->unique('name');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('condition');
        });

        foreach (DB::table('cars')->cursor() as $car) {
            DB::table('cars')->where('car_id', $car->car_id)->update([
                'brand' => $car->brand_label,
            ]);
        }

        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'brand_label']);
        });
    }
};
