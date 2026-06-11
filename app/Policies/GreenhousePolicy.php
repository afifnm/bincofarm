<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Greenhouse;
use App\Models\User;

class GreenhousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isPjGh();
    }

    public function view(User $user, Greenhouse $greenhouse): bool
    {
        return $user->isAdmin() || $greenhouse->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Greenhouse $greenhouse): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Greenhouse $greenhouse): bool
    {
        return $user->isAdmin();
    }

    /**
     * Operasional GH: catat panen, update populasi pohon, penjualan melon.
     * Admin bebas; PJ GH hanya pada GH yang ditugaskan padanya.
     */
    public function manage(User $user, Greenhouse $greenhouse): bool
    {
        return $user->isAdmin() || $greenhouse->user_id === $user->id;
    }
}
