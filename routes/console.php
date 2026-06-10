<?php

declare(strict_types=1);

use App\Services\PeriodeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;

// Setiap tanggal 1 jam 00:05 — generate snapshot draft bulan lalu
Schedule::call(function (): void {
    $prevMonth = Carbon::now()->subMonth()->startOfMonth();
    app(PeriodeService::class)->generateDraftSnapshot($prevMonth);
})->monthlyOn(1, '00:05');
