<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_barang', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('barang_id')->constrained('barang')->restrictOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar', 'penyesuaian']);
            $table->decimal('qty', 14, 2);
            $table->decimal('harga_satuan', 18, 2)->default(0);
            $table->decimal('stok_setelah', 14, 2)->default(0);
            $table->string('referensi')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_void')->default(false);
            $table->timestamp('void_at')->nullable();
            $table->timestamps();

            $table->index(['barang_id', 'tanggal']);
            $table->index(['tanggal', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_barang');
    }
};
