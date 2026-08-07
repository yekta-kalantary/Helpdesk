<?php

namespace Modules\Reports\Application\Queries;

use Illuminate\Support\Facades\DB;

class OperationalReport
{
    public function get(): array
    {
        return [
            'customers' => DB::table('customers')
                ->whereNull('customers.deleted_at')
                ->select(['customers.id', 'customers.name', 'customers.company', 'customers.status'])
                ->addSelect([
                    'projects_count' => DB::table('projects')->selectRaw('count(*)')->whereColumn('projects.customer_id', 'customers.id')->whereNull('projects.deleted_at'),
                    'open_tickets_count' => DB::table('tickets')->selectRaw('count(*)')->whereColumn('tickets.customer_id', 'customers.id')->whereNull('tickets.deleted_at')->where('tickets.status', '!=', 'closed'),
                ])
                ->orderBy('customers.name')
                ->get(),
            'projects' => DB::table('projects')
                ->join('customers', 'customers.id', '=', 'projects.customer_id')
                ->whereNull('projects.deleted_at')
                ->whereNull('customers.deleted_at')
                ->select(['projects.id', 'projects.title', 'projects.status', 'customers.name as customer_name'])
                ->addSelect([
                    'tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at'),
                    'done_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at')->where('tasks.status', 'done'),
                    'overdue_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.project_id', 'projects.id')->whereNull('tasks.deleted_at')->whereNotIn('tasks.status', ['done', 'cancelled'])->whereNotNull('tasks.due_at')->where('tasks.due_at', '<', now()),
                ])
                ->orderBy('customers.name')
                ->orderBy('projects.title')
                ->get(),
            'team' => DB::table('users')
                ->where('users.is_active', true)
                ->whereNotExists(fn ($query) => $query->selectRaw('1')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->where('roles.name', 'customer'))
                ->select(['users.id', 'users.name', 'users.email'])
                ->addSelect([
                    'assigned_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at'),
                    'done_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at')->where('tasks.status', 'done'),
                    'overdue_tasks_count' => DB::table('tasks')->selectRaw('count(*)')->whereColumn('tasks.assigned_to', 'users.id')->whereNull('tasks.deleted_at')->whereNotIn('tasks.status', ['done', 'cancelled'])->whereNotNull('tasks.due_at')->where('tasks.due_at', '<', now()),
                ])
                ->orderBy('users.name')
                ->get(),
        ];
    }
}
