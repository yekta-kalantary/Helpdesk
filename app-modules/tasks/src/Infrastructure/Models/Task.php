<?php

namespace Modules\Tasks\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Task extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'description', 'assigned_to', 'created_by', 'priority', 'status',
        'is_customer_visible', 'due_at', 'estimated_minutes', 'spent_minutes',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'is_customer_visible' => 'boolean',
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

    public function registerMediaConversions(?Media $media = null): void
    {
        // Originals only: no image processing dependency is required at runtime.
    }
}
