<?php

namespace Modules\Customers\Infrastructure\Models;

use App\Models\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'notes',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
