<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panen_melon', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained('greenhouses')->restrictOnDelete();
            $table->foreignId('jenis_melon_id')->constrained('jenis_melon')->restrictOnDelete();
            $table->decimal('berat', 8, 2);
            $table->enum('grade', ['A', 'B', 'C', 'D', 'E']);
            $table->boolean('is_busuk')->default(false);
            $table->date('tanggal');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['greenhouse_id', 'tanggal']);
            $table->index(['greenhouse_id', 'jenis_melon_id', 'grade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panen_melon');
    }
};
