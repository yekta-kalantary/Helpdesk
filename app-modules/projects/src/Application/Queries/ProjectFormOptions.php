<?php

namespace Modules\Projects\Application\Queries;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectFormOptions
{
    /** @return array<string,array<int,array{id:int,name:string}>> */
    public function get(): array
    {
        return [
            'customers' => DB::table('customers')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (object $row) => ['id' => $row->id, 'name' => $row->name])
                ->all(),
            'members' => User::query()
                ->where('is_active', true)
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name])
                ->all(),
        ];
    }
}
