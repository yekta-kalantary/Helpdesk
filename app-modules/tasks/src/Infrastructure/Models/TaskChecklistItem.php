<?php

namespace Modules\Tasks\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Identity\Infrastructure\Models\User;

class TaskChecklistItem extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
        'position',
        'created_by',
        'removed_at',
    ];

    protected $attributes = [
        'is_completed' => false,
        'position' => 0,
    ];

    protected static function booted(): void
    {
        static::deleting(function (): never {
            throw new DomainException('Task checklist items cannot be hard-deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'removed_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isRemoved(): bool
    {
        return $this->removed_at !== null;
    }
}
