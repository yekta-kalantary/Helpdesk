<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxMessage extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'event_version',
        'correlation_id',
        'occurred_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'event_version' => 'integer',
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
