<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perlebar dulu agar nilai lama tidak terpotong saat dikonversi
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'inventory'");
        DB::table('users')->whereNotIn('role', ['admin', 'inventory', 'pj_gh'])->update(['role' => 'inventory']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'inventory', 'pj_gh') NOT NULL DEFAULT 'inventory'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'kasir'");
        DB::table('users')->whereNotIn('role', ['admin'])->update(['role' => 'kasir']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir'");
    }
};
