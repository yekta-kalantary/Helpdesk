<?php

namespace Modules\Tasks\Application\Queries;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskAccessScope
{
    /** @return array{actor_id:int,customer_id:?int,manage_all:bool} */
    public function for(User $user): array
    {
        $customerId = $user->hasRole('customer')
            ? DB::table('customers')->where('user_id', $user->id)->whereNull('deleted_at')->value('id')
            : null;

        return [
            'actor_id' => $user->id,
            'customer_id' => $customerId ? (int) $customerId : null,
            'manage_all' => $user->hasRole('admin') || $user->can('tasks.manage_all'),
        ];
    }
}
