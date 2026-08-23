<?php

namespace Modules\Audit\Infrastructure\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'project_id',
        'task_id',
        'action',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function scopeWithoutModeration(Builder $query): Builder
    {
        return $query->whereNotIn('action', ['comment.hidden', 'attachment.hidden']);
    }
}
