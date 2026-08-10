<?php

namespace Modules\Tasks\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Media\Infrastructure\Concerns\InteractsWithMedia;
use Modules\Media\Infrastructure\Contracts\MediaOwner;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;

class Task extends Model implements MediaOwner
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'description', 'assigned_to', 'created_by', 'priority', 'status',
        'due_at', 'estimated_minutes', 'spent_minutes',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_at' => 'datetime',
            'estimated_minutes' => 'integer',
            'spent_minutes' => 'integer',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('local');
    }
}
