<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas', function (Blueprint $table): void {
            $table->dropUnique(['kode']);
            $table->dropColumn('kode');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->string('kode')->unique()->after('id');
        });
    }
};
