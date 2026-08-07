<?php

namespace Modules\Tickets\Application\Queries;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketFormOptions
{
    public function get(User $user, array $scope): array
    {
        $customers = $scope['customer_id']
            ? DB::table('customers')->where('id', $scope['customer_id'])->get(['id', 'name'])
            : DB::table('customers')->whereNull('deleted_at')->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $projects = DB::table('projects')
            ->join('customers', 'customers.id', '=', 'projects.customer_id')
            ->whereNull('projects.deleted_at')
            ->when($scope['customer_id'], fn ($query) => $query->where('projects.customer_id', $scope['customer_id']))
            ->orderBy('customers.name')->orderBy('projects.title')
            ->get(['projects.id', 'projects.customer_id', 'projects.title', 'customers.name as customer_name']);

        $members = User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'customers' => $customers->map(fn (object $row) => ['id' => $row->id, 'name' => $row->name])->all(),
            'projects' => $projects->map(fn (object $row) => ['id' => $row->id, 'customer_id' => $row->customer_id, 'name' => $row->customer_name.' — '.$row->title])->all(),
            'members' => $members->map(fn (User $member) => ['id' => $member->id, 'name' => $member->name])->all(),
        ];
    }
}
