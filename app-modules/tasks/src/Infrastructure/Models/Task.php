<?php

namespace Modules\Tasks\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Modules\Projects\Infrastructure\Models\WorkGroup;
use Modules\Tasks\Domain\Enums\TaskPriority;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'project_status_id',
        'work_group_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'due_date',
        'completed_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function fill(array $attributes)
    {
        if ($this->exists
            && array_key_exists('reference', $attributes)
            && $attributes['reference'] !== $this->reference) {
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

        static::saving(function (Task $task): void {
            if (! $task->exists || $task->isDirty(['project_status_id', 'work_group_id', 'assigned_to'])) {
                $task->assertStateIntegrity();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectTaskStatus::class, 'project_status_id');
    }

    public function workGroup(): BelongsTo
    {
        return $this->belongsTo(WorkGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('project', fn (Builder $projects) => $projects->visibleTo($user));
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereDate('due_date', '<', today())
            ->whereHas('projectStatus', fn (Builder $statuses): Builder => $statuses->where('is_done', false));
    }

    public function isDone(): bool
    {
        $this->loadMissing('projectStatus');

        return (bool) $this->projectStatus?->is_done;
    }

    public function isTerminal(): bool
    {
        return $this->isDone();
    }

    private function assertStateIntegrity(): void
    {
        if (! $this->project_id) {
            throw new DomainException('Task project is required.');
        }

        $status = ProjectTaskStatus::query()->find($this->project_status_id);
        if (! $status || ! $status->is_active || $status->project_id !== (int) $this->project_id) {
            throw new DomainException('Task Project Status must be active and belong to the same Project.');
        }

        if ($this->work_group_id) {
            $group = WorkGroup::query()->find($this->work_group_id);
            if (! $group || $group->project_id !== (int) $this->project_id) {
                throw new DomainException('Task Work Group must belong to the same Project.');
            }

            if ((! $this->exists || $this->isDirty('work_group_id')) && ! $group->isActive()) {
                throw new DomainException('A new Task Work Group assignment must target an active Work Group.');
            }
        }

        if (! $this->assigned_to) {
            return;
        }

        $assignee = User::query()->find($this->assigned_to);
        if (! $assignee || ! $assignee->is_active) {
            throw new DomainException('Task assignee must be active.');
        }

        if ($assignee->isAdmin()) {
            return;
        }

        if ($assignee->isCustomer()) {
            if (! $assignee->client_id) {
                throw new DomainException('Task assignee has an invalid customer account.');
            }

            $project = Project::query()->find($this->project_id);
            if (! $project || $project->client_id !== $assignee->client_id || ! $project->hasActiveMember($assignee)) {
                throw new DomainException('Customer assignee must be an active member of the task Project.');
            }

            return;
        }

        if ($assignee->isEmployee()) {
            if (! $assignee->client_id) {
                throw new DomainException('Task assignee has an invalid employee account.');
            }

            $project = Project::query()->find($this->project_id);
            if (! $project || $project->client_id !== $assignee->client_id || ! $project->hasActiveMember($assignee)) {
                throw new DomainException('Employee assignee must be an active member of the task Project.');
            }

            return;
        }

        if (! $assignee->client_id) {
            throw new DomainException('Task assignee has an invalid role.');
        }

        throw new DomainException('Task assignee has an invalid role.');
    }
}
