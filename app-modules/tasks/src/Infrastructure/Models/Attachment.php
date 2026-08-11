<?php

namespace Modules\Tasks\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Identity\Infrastructure\Models\User;

class Attachment extends Model
{
    protected $fillable = [
        'task_id',
        'comment_id',
        'uploaded_by',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'hidden_at',
        'hidden_by',
    ];

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'comment_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }
}
