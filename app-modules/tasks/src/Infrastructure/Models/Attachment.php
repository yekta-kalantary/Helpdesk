<?php

namespace Modules\Tasks\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, config('helpdesk.attachments.mime_types', []), true)
            && in_array($this->mime_type, config('helpdesk.attachments.preview_mime_types', []), true);
    }
}
