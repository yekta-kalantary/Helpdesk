<?php

namespace Modules\Projects\Infrastructure\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Tasks\Infrastructure\Models\Task;

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
        static::created(function (Project $project): void {
            if (! Schema::hasTable('project_task_statuses')) {
                return;
            }

            $creatorId = auth()->id()
                ?? User::query()->active()->admins()->orderBy('id')->value('id')
                ?? User::query()->orderBy('id')->value('id');

            foreach ([
                ['title' => 'باز', 'position' => 10, 'is_done' => false],
                ['title' => 'در حال انجام', 'position' => 20, 'is_done' => false],
                ['title' => 'انجام‌شده', 'position' => 30, 'is_done' => true],
            ] as $status) {
                $project->taskStatuses()->create($status + [
                    'created_by' => $creatorId,
                    'is_active' => true,
                ]);
            }
        });

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['joined_at', 'removed_at'])
            ->withTimestamps();
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->wherePivotNull('removed_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(ProjectTaskStatus::class)->orderBy('position')->orderBy('id');
    }

    public function workGroups(): HasMany
    {
        return $this->hasMany(WorkGroup::class)->orderBy('position')->orderBy('id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->canAuthenticate() || ! $user->client_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('client_id', $user->client_id)
            ->whereHas('members', fn (Builder $members) => $members
                ->whereKey($user->id)
                ->whereNull('project_user.removed_at'));
    }

    public function hasActiveMember(User $user): bool
    {
        return $this->activeMembers()->whereKey($user->id)->exists();
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
