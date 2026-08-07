<?php

namespace Modules\Tasks\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskAccessScope
{
    /** @return array{actor_id:int,customer_id:?int,manage_all:bool} */
    public function for(User $user): array
    {
        $customerId = $user->person?->type === PersonType::Customer
            ? DB::table('customers')->where('person_id', $user->person_id)->whereNull('deleted_at')->value('id')
            : null;

        return [
            'actor_id' => $user->id,
            'customer_id' => $customerId ? (int) $customerId : null,
            'manage_all' => $user->hasRole('admin') || $user->can('tasks.manage_all'),
        ];
    }
}
