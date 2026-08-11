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
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

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
            if (! $task->exists || $task->isDirty(['status', 'assigned_to'])) {
                $task->assertStateIntegrity();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
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
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    private function assertStateIntegrity(): void
    {
        $status = $this->status instanceof TaskStatus
            ? $this->status
            : TaskStatus::from((string) $this->status);

        $assignee = $this->assigned_to
            ? User::query()->find($this->assigned_to)
            : null;

        if ($status === TaskStatus::WaitingAdmin) {
            if ($assignee && (! $assignee->isAdmin() || ! $assignee->is_active)) {
                throw new DomainException('Waiting Admin tasks may only be unassigned or assigned to an active admin.');
            }

            return;
        }

        if ($status === TaskStatus::WaitingCustomer) {
            if (! $assignee || ! $this->validCustomerAssignee($assignee)) {
                throw new DomainException('Waiting Customer requires an active customer member from the same project.');
            }

            return;
        }

        if (in_array($status, [TaskStatus::Todo, TaskStatus::InProgress], true)) {
            if (! $assignee || ! $assignee->is_active) {
                throw new DomainException('Todo and In Progress tasks require an active assignee.');
            }

            if ($assignee->isCustomer() && ! $this->validCustomerAssignee($assignee)) {
                throw new DomainException('Customer assignee must be an active member of the task project.');
            }

            if (! $assignee->isAdmin() && ! $assignee->isCustomer()) {
                throw new DomainException('Task assignee has an invalid role.');
            }
        }
    }

    private function validCustomerAssignee(User $user): bool
    {
        if (! $user->isCustomer() || ! $user->is_active || ! $user->client_id) {
            return false;
        }

        $project = $this->project()->first();

        return $project !== null
            && $project->client_id === $user->client_id
            && $project->hasActiveMember($user);
    }
}
