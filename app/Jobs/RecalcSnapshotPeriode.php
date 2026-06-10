<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\PeriodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalcSnapshotPeriode implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly int    $entityId,
        private readonly string $fromPeriode,
    ) {}

    public function handle(PeriodeService $service): void
    {
        if ($this->type === 'kas') {
            $service->recalcKasSince($this->entityId, $this->fromPeriode);
        } else {
            $service->recalcBarangSince($this->entityId, $this->fromPeriode);
        }
    }
}
