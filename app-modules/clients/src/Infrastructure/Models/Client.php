<?php

namespace Modules\Clients\Infrastructure\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Domain\Enums\ClientStatus;

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ClientStatus::Active->value);
    }

    public function isActive(): bool
    {
        return $this->status === ClientStatus::Active;
    }
}
