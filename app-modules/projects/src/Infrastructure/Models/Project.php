<?php

namespace Modules\Projects\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Projects\Domain\Enums\ProjectStatus;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'start_date',
        'due_date',
    ];

    protected static function booted(): void
    {
        static::updating(function (Project $project): void {
            if ($project->isDirty('client_id')) {
                throw new DomainException('Project client is immutable after creation.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(ProjectTaskStatus::class)->orderBy('position')->orderBy('id');
    }

    public function workGroups(): HasMany
    {
        return $this->hasMany(WorkGroup::class)->orderBy('position')->orderBy('id');
    }

    public function isActive(): bool
    {
        return $this->status === ProjectStatus::Active;
    }

    public function doneTaskStatus(): ?ProjectTaskStatus
    {
        return $this->taskStatuses()->active()->where('is_done', true)->first();
    }

    public function defaultOpenTaskStatus(): ?ProjectTaskStatus
    {
        return $this->taskStatuses()->active()->where('is_done', false)->orderBy('position')->first();
    }
}
