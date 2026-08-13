<?php

namespace Modules\Clients\Infrastructure\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

#[Fillable(['name', 'description', 'status'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ClientStatus::Active->value);
    }

    public function isActive(): bool
    {
        return $this->status === ClientStatus::Active;
    }
}
