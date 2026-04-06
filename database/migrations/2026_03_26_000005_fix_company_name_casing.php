<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix any companies whose names were stored in wrong case
        $corrections = [
            'scania' => 'Scania',
            'toyota' => 'Toyota',
            'subaru' => 'Subaru',
            'ford' => 'Ford',
            'range rover' => 'Range Rover',
            'land rover' => 'Land Rover',
            'bmw' => 'BMW',
            'suzuki' => 'Suzuki',
            'sinotruck' => 'Sinotruck',
        ];

        foreach ($corrections as $old => $new) {
            DB::table('companies')
                ->whereRaw('LOWER(name) = ?', [$old])
                ->where('name', '!=', $new)
                ->update(['name' => $new]);
        }
    }

    public function down(): void {}
};
