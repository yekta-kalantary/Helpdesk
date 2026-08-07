<?php

namespace Modules\Tickets\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketFormOptions
{
    public function get(User $user, array $scope): array
    {
        $accessibleProjectIds = null;

        if (! $scope['customer_id'] && ! $scope['manage_all']) {
            $accessibleProjectIds = DB::table('project_user')
                ->where('user_id', $user->id)
                ->select('project_id');
        }

        $projects = DB::table('projects')
            ->join('customers', 'customers.id', '=', 'projects.customer_id')
            ->join('people as customer_people', 'customer_people.id', '=', 'customers.person_id')
            ->whereNull('projects.deleted_at')
            ->whereNull('customers.deleted_at')
            ->when($scope['customer_id'], fn ($query) => $query->where('projects.customer_id', $scope['customer_id']))
            ->when($accessibleProjectIds, fn ($query) => $query->whereIn('projects.id', $accessibleProjectIds))
            ->orderBy('customer_people.first_name')
            ->orderBy('customer_people.last_name')
            ->orderBy('projects.title')
            ->get([
                'projects.id', 'projects.customer_id', 'projects.title',
                'customer_people.first_name as customer_first_name', 'customer_people.last_name as customer_last_name',
            ]);

        $customersQuery = DB::table('customers')
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->whereNull('customers.deleted_at');

        if ($scope['customer_id']) {
            $customers = $customersQuery
                ->where('customers.id', $scope['customer_id'])
                ->get(['customers.id', 'people.first_name', 'people.last_name']);
        } elseif ($scope['manage_all']) {
            $customers = $customersQuery
                ->where('customers.status', 'active')
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get(['customers.id', 'people.first_name', 'people.last_name']);
        } else {
            $customerIds = $projects->pluck('customer_id')->unique()->values();
            $customers = $customersQuery
                ->whereIn('customers.id', $customerIds)
                ->where('customers.status', 'active')
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get(['customers.id', 'people.first_name', 'people.last_name']);
        }

        $members = User::query()
            ->select('users.*')
            ->join('people', 'people.id', '=', 'users.person_id')
            ->with('person')
            ->where('users.is_active', true)
            ->where('people.type', PersonType::Employee->value)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->orderBy('people.first_name')
            ->orderBy('people.last_name')
            ->get();

        return [
            'customers' => $customers->map(fn (object $row) => [
                'id' => $row->id,
                'name' => trim($row->first_name.' '.$row->last_name),
            ])->all(),
            'projects' => $projects->map(fn (object $row) => [
                'id' => $row->id,
                'customer_id' => $row->customer_id,
                'name' => trim($row->customer_first_name.' '.$row->customer_last_name).' — '.$row->title,
            ])->all(),
            'members' => $members->map(fn (User $member) => ['id' => $member->id, 'name' => $member->full_name])->all(),
        ];
    }
}
