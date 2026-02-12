<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE `cars` SET `car_pic` = JSON_ARRAY(`car_pic`) WHERE `car_pic` IS NOT NULL AND JSON_VALID(`car_pic`) = 0");
        DB::statement('ALTER TABLE `cars` MODIFY `car_pic` JSON NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `cars` SET `car_pic` = JSON_UNQUOTE(JSON_EXTRACT(`car_pic`, '$[0]')) WHERE `car_pic` IS NOT NULL AND JSON_VALID(`car_pic`) = 1");
        DB::statement('ALTER TABLE `cars` MODIFY `car_pic` VARCHAR(255) NULL');
    }
};
