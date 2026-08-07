<?php

namespace Modules\Tickets\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Tickets\Domain\Enums\TicketCategory;
use Modules\Tickets\Domain\Enums\TicketPriority;
use Modules\Tickets\Domain\Enums\TicketStatus;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'project_id', 'created_by', 'assigned_to', 'subject', 'category', 'priority', 'status',
    ];

    protected function casts(): array
    {
        return [
            'category' => TicketCategory::class,
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
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

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->oldest('id');
    }
}
