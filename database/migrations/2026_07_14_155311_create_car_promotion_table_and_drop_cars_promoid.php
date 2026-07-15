<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_promotion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('promoID');
            $table->timestamps();

            $table->unique(['car_id', 'promoID']);
            $table->foreign('car_id')->references('car_id')->on('cars')->cascadeOnDelete();
            $table->foreign('promoID')->references('promoID')->on('promotions')->cascadeOnDelete();
        });

        // Move any existing single-promo links into the pivot
        if (Schema::hasColumn('cars', 'promoID')) {
            $rows = DB::table('cars')
                ->whereNotNull('promoID')
                ->get(['car_id', 'promoID']);

            $now = now();
            foreach ($rows as $row) {
                DB::table('car_promotion')->insertOrIgnore([
                    'car_id' => $row->car_id,
                    'promoID' => $row->promoID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            Schema::table('cars', function (Blueprint $table) {
                $table->dropForeign(['promoID']);
                $table->dropColumn('promoID');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedBigInteger('promoID')->nullable()->after('location');
            $table->foreign('promoID')
                ->references('promoID')
                ->on('promotions')
                ->nullOnDelete();
        });

        // Restore only the first linked promo per car
        $links = DB::table('car_promotion')
            ->select('car_id', 'promoID')
            ->orderBy('id')
            ->get()
            ->unique('car_id');

        foreach ($links as $link) {
            DB::table('cars')->where('car_id', $link->car_id)->update([
                'promoID' => $link->promoID,
            ]);
        }

        Schema::dropIfExists('car_promotion');
    }
};
