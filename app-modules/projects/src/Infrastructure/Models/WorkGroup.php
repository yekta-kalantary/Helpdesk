<?php

namespace Modules\Projects\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkGroup extends Model
{
    protected $fillable = [
        'project_id',
        'parent_id',
        'title',
        'description',
        'position',
        'status',
        'created_by',
        'inactivated_at',
    ];

    protected $attributes = [
        'position' => 0,
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::deleting(function (): never {
            throw new DomainException('Work Groups cannot be hard-deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'inactivated_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function depth(): int
    {
        $depth = 1;
        $parent = $this->parent;
        $visited = [$this->id => true];

        while ($parent !== null) {
            if (isset($visited[$parent->id])) {
                throw new DomainException('Work Group hierarchy contains a cycle.');
            }

            $visited[$parent->id] = true;
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
