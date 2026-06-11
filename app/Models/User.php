<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'phone', 'avatar', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin->value;
    }

    public function isInventory(): bool
    {
        return $this->role === Role::Inventory->value;
    }

    public function isPjGh(): bool
    {
        return $this->role === Role::PjGh->value;
    }

    /**
     * Greenhouse yang ditugaskan ke user ini (sebagai penanggung jawab).
     */
    public function greenhouses(): HasMany
    {
        return $this->hasMany(Greenhouse::class);
    }

    /**
     * Admin bisa akses semua GH; PJ GH hanya GH yang ditugaskan padanya.
     */
    public function canAccessGreenhouse(int $greenhouseId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->greenhouses()->whereKey($greenhouseId)->exists();
    }

    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class);
    }

    public function mutasiBarang(): HasMany
    {
        return $this->hasMany(MutasiBarang::class);
    }
}
