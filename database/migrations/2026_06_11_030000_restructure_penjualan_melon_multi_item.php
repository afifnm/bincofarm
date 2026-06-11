<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header: hapus kolom item tunggal, tambah nomor nota
        Schema::table('penjualan_melon', function (Blueprint $table): void {
            $table->dropForeign(['jenis_melon_id']);
            $table->dropIndex(['greenhouse_id', 'jenis_melon_id']);
            $table->dropColumn(['jenis_melon_id', 'jumlah_kg', 'harga_per_kg']);
        });

        Schema::table('penjualan_melon', function (Blueprint $table): void {
            $table->string('no_nota', 30)->nullable()->unique()->after('id');
        });

        // Detail item per nota
        Schema::create('penjualan_melon_item', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('penjualan_melon_id')->constrained('penjualan_melon')->cascadeOnDelete();
            $table->foreignId('jenis_melon_id')->constrained('jenis_melon')->restrictOnDelete();
            $table->decimal('jumlah_kg', 8, 2);
            $table->decimal('harga_per_kg', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();

            $table->index('jenis_melon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_melon_item');

        Schema::table('penjualan_melon', function (Blueprint $table): void {
            $table->dropColumn('no_nota');
            $table->foreignId('jenis_melon_id')->nullable()->constrained('jenis_melon')->restrictOnDelete();
            $table->decimal('jumlah_kg', 8, 2)->default(0);
            $table->decimal('harga_per_kg', 18, 2)->default(0);
            $table->index(['greenhouse_id', 'jenis_melon_id']);
        });
    }
};
