<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan_melon', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained('greenhouses')->restrictOnDelete();
            $table->string('nama_pembeli');
            $table->foreignId('jenis_melon_id')->constrained('jenis_melon')->restrictOnDelete();
            $table->decimal('jumlah_kg', 8, 2);
            $table->decimal('harga_per_kg', 18, 2);
            $table->decimal('total', 18, 2);
            $table->date('tanggal');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['greenhouse_id', 'tanggal']);
            $table->index(['greenhouse_id', 'jenis_melon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_melon');
    }
};
