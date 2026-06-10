<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_periode', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kas_id')->constrained('kas')->restrictOnDelete();
            $table->date('periode');
            $table->decimal('saldo_akhir', 18, 2)->default(0);
            $table->decimal('total_masuk', 18, 2)->default(0);
            $table->decimal('total_keluar', 18, 2)->default(0);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kas_id', 'periode']);
            $table->index(['kas_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_periode');
    }
};
