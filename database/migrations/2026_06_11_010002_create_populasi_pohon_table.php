<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('populasi_pohon', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->unique()->constrained('greenhouses')->cascadeOnDelete();
            $table->unsignedInteger('total_pohon')->default(0);
            $table->unsignedInteger('pohon_hidup')->default(0);
            $table->unsignedInteger('pohon_mati')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('populasi_pohon');
    }
};
