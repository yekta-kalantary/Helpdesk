<?php

namespace Modules\Projects\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;

class ProjectTaskStatus extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'position',
        'is_done',
        'is_active',
        'created_by',
        'inactivated_at',
    ];

    protected $attributes = [
        'position' => 0,
        'is_done' => false,
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::deleting(function (): never {
            throw new DomainException('Project Task Statuses cannot be hard-deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'is_active' => 'boolean',
            'inactivated_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_status_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
