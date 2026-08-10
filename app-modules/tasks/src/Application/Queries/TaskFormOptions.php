<?php

namespace Modules\Tasks\Application\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;

class TaskFormOptions
{
    /** @return array<string,array<int,array{id:int,name:string}>> */
    public function get(User $user, array $scope, ?int $projectId = null): array
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
            ->join('contacts', 'contacts.id', '=', 'users.contact_id')
            ->with('contact')
            ->where('users.is_active', true)
            ->when(
                $projectId,
                fn ($query) => $query->whereIn('users.id', DB::table('project_user')->where('project_id', $projectId)->select('user_id')),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->orderBy('contacts.first_name')
            ->orderBy('contacts.last_name')
            ->get()
            ->map(fn (User $member) => ['id' => $member->id, 'name' => $member->full_name])
            ->all();

        return compact('projects', 'members');
    }

    public function isAssignableToProject(int $projectId, int $userId): bool
    {
        return User::query()
            ->whereKey($userId)
            ->where('is_active', true)
            ->whereIn('id', DB::table('project_user')->where('project_id', $projectId)->select('user_id'))
            ->exists();
    }
}
