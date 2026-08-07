<?php

namespace Modules\Projects\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectFormOptions
{
    /** @return array<string,array<int,array{id:int,name:string}>> */
    public function get(): array
    {
        return [
            'customers' => DB::table('customers')
                ->join('people', 'people.id', '=', 'customers.person_id')
                ->whereNull('customers.deleted_at')
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get(['customers.id', 'people.first_name', 'people.last_name'])
                ->map(fn (object $row) => ['id' => $row->id, 'name' => trim($row->first_name.' '.$row->last_name)])
                ->all(),
            'members' => User::query()
                ->select('users.*')
                ->join('people', 'people.id', '=', 'users.person_id')
                ->with('person')
                ->where('users.is_active', true)
                ->where('people.type', PersonType::Employee->value)
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get()
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->full_name])
                ->all(),
        ];
    }
}
