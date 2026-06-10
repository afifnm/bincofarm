<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_kas', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('kas_id')->constrained('kas')->restrictOnDelete();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_transaksi')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar', 'transfer_masuk', 'transfer_keluar']);
            $table->decimal('jumlah', 18, 2);
            $table->string('keterangan')->nullable();
            $table->uuid('transfer_group')->nullable()->index();
            $table->nullableMorphs('sumber');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_void')->default(false);
            $table->timestamp('void_at')->nullable();
            $table->timestamps();

            $table->index(['kas_id', 'tanggal']);
            $table->index(['tanggal', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas');
    }
};
