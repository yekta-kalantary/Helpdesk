<?php

namespace Modules\Tasks\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Tasks\Domain\Enums\TaskPriority;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'project_status_id', 'work_group_id', 'created_by', 'assigned_to',
        'title', 'description', 'priority', 'due_date', 'completed_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function fill(array $attributes)
    {
        if ($this->exists && array_key_exists('reference', $attributes) && $attributes['reference'] !== $this->reference) {
            throw new DomainException('Task reference is immutable after creation.');
        }

        return parent::fill($attributes);
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task): void {
            if (blank($task->reference)) {
                do {
                    $task->reference = 'TSK-'.Str::upper(Str::random(8));
                } while (static::query()->where('reference', $task->reference)->exists());
            }
        });

        static::updating(function (Task $task): void {
            if ($task->isDirty('project_id')) {
                throw new DomainException('Task project is immutable after creation.');
            }

            if ($task->isDirty('reference')) {
                throw new DomainException('Task reference is immutable after creation.');
            }
        });
    }

    protected function casts(): array
    {
        return ['priority' => TaskPriority::class, 'due_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->whereNull('comment_id')->orderBy('id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->whereNull('removed_at')->orderBy('position')->orderBy('id');
    }

    public function allChecklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position')->orderBy('id');
    }
}
