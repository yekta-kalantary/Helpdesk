<?php

namespace Modules\Tasks\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskFormOptions
{
    /** @return array<string,array<int,array{id:int,name:string}>> */
    public function get(User $user, array $scope): array
    {
        $projects = DB::table('projects')
            ->whereNull('deleted_at')
            ->when(! $scope['manage_all'], fn ($query) => $query->whereIn('id', DB::table('project_user')->where('user_id', $user->id)->select('project_id')))
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (object $row) => ['id' => $row->id, 'name' => $row->title])
            ->all();

        $members = User::query()
            ->select('users.*')
            ->join('people', 'people.id', '=', 'users.person_id')
            ->with('person')
            ->where('users.is_active', true)
            ->where('people.type', PersonType::Employee->value)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->orderBy('people.first_name')
            ->orderBy('people.last_name')
            ->get()
            ->map(fn (User $member) => ['id' => $member->id, 'name' => $member->full_name])
            ->all();

        return compact('projects', 'members');
    }
}
