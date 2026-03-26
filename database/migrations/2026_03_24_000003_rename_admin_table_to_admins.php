<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin') && ! Schema::hasTable('admins')) {
            Schema::rename('admin', 'admins');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admins') && ! Schema::hasTable('admin')) {
            Schema::rename('admins', 'admin');
        }
    }
};
