<?php

namespace Modules\Reports\Application\Queries;

use App\Enums\PersonType;
use Illuminate\Support\Facades\DB;

class OperationalReport
{
    public function get(): array
    {
        return [
            'customers' => DB::table('customers')
                ->join('people', 'people.id', '=', 'customers.person_id')
                ->whereNull('customers.deleted_at')
                ->select(['customers.id', 'customers.status', 'people.first_name', 'people.last_name'])
                ->addSelect([
                    'projects_count' => DB::table('projects')->selectRaw('count(*)')->whereColumn('projects.customer_id', 'customers.id')->whereNull('projects.deleted_at'),
                    'open_tickets_count' => DB::table('tickets')->selectRaw('count(*)')->whereColumn('tickets.customer_id', 'customers.id')->whereNull('tickets.deleted_at')->where('tickets.status', '!=', 'closed'),
                ])
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get(),
            'projects' => DB::table('projects')
                ->join('customers', 'customers.id', '=', 'projects.customer_id')
                ->join('people', 'people.id', '=', 'customers.person_id')
                ->whereNull('projects.deleted_at')
                ->whereNull('customers.deleted_at')
                ->select([
                    'projects.id', 'projects.title', 'projects.status',
                    'people.first_name as customer_first_name', 'people.last_name as customer_last_name',
                ])
                ->addSelect([
                    'tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at'),
                    'done_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at')->where('tasks.status', 'done'),
                    'overdue_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at')->whereNotIn('tasks.status', ['done', 'cancelled'])->whereNotNull('tasks.due_at')->where('tasks.due_at', '<', now()),
                ])
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->orderBy('projects.title')
                ->get(),
            'team' => DB::table('users')
                ->join('people', 'people.id', '=', 'users.person_id')
                ->where('users.is_active', true)
                ->where('people.type', PersonType::Employee->value)
                ->whereNotExists(fn ($query) => $query->selectRaw('1')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', 'App\Models\User')
                    ->where('roles.name', 'customer'))
                ->select(['users.id', 'people.first_name', 'people.last_name', 'people.email'])
                ->addSelect([
                    'assigned_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at'),
                    'done_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at')->where('tasks.status', 'done'),
                    'overdue_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at')->whereNotIn('tasks.status', ['done', 'cancelled'])->whereNotNull('tasks.due_at')->where('tasks.due_at', '<', now()),
                ])
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get(),
        ];
    }
}
