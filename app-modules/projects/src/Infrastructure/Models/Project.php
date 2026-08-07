<?php

namespace Modules\Projects\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Projects\Domain\Enums\ProjectCategory;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Domain\Enums\ProjectType;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'category',
        'title',
        'type',
        'description',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => ProjectCategory::class,
            'type' => ProjectType::class,
            'status' => ProjectStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }
}
