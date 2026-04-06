<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add company_id FK column to cars
        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('car_id')
                ->constrained('companies')
                ->nullOnDelete();
        });

        // Step 2: Seed companies table from existing unique company names
        $names = DB::table('cars')
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->distinct()
            ->pluck('company');

        foreach ($names as $name) {
            DB::table('companies')->insertOrIgnore([
                'name' => $name,
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Step 3: Populate company_id on cars from the seeded companies
        $companies = DB::table('companies')->pluck('id', 'name');
        foreach ($companies as $name => $id) {
            DB::table('cars')->where('company', $name)->update(['company_id' => $id]);
        }

        // Step 4: Drop the old company and company_logo columns
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['company', 'company_logo']);
        });
    }

    public function down(): void
    {
        // Restore old columns
        Schema::table('cars', function (Blueprint $table) {
            $table->string('company')->nullable()->after('brand');
            $table->string('company_logo')->nullable()->after('company');
        });

        // Re-populate company name from relationship
        $cars = DB::table('cars')->whereNotNull('company_id')->get();
        foreach ($cars as $car) {
            $company = DB::table('companies')->find($car->company_id);
            if ($company) {
                DB::table('cars')->where('car_id', $car->car_id)->update([
                    'company' => $company->name,
                    'company_logo' => $company->logo,
                ]);
            }
        }

        // Drop company_id
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
