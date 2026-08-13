<?php

namespace Modules\Tasks\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Identity\Infrastructure\Models\User;

class TaskComment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'body',
        'hidden_at',
        'hidden_by',
    ];

    protected function casts(): array
    {
        return ['hidden_at' => 'datetime'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'comment_id');
    }
}
