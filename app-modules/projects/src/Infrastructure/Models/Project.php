<?php

namespace Modules\Projects\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Identity\Infrastructure\Models\User;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }
}
