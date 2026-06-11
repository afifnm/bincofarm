<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('populasi_pohon_histori', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained('greenhouses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_pohon_lama')->default(0);
            $table->unsignedInteger('pohon_hidup_lama')->default(0);
            $table->unsignedInteger('pohon_mati_lama')->default(0);
            $table->unsignedInteger('total_pohon_baru');
            $table->unsignedInteger('pohon_hidup_baru');
            $table->unsignedInteger('pohon_mati_baru');
            $table->string('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('greenhouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('populasi_pohon_histori');
    }
};
