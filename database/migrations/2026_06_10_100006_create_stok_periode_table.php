<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_periode', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->date('periode');
            $table->decimal('stok_akhir', 14, 2)->default(0);
            $table->decimal('total_masuk', 14, 2)->default(0);
            $table->decimal('total_keluar', 14, 2)->default(0);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['barang_id', 'periode']);
            $table->index(['barang_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_periode');
    }
};
