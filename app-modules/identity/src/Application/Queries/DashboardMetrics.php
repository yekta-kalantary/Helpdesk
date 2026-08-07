<?php

namespace Modules\Identity\Application\Queries;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetrics
{
    /** @return array<string,int> */
    public function for(User $user): array
    {
        if ($user->hasRole('customer')) {
            $customerId = Schema::hasTable('customers')
                ? DB::table('customers')->where('user_id', $user->id)->value('id')
                : null;

            return [
                'projects' => $customerId && Schema::hasTable('projects') ? DB::table('projects')->where('customer_id', $customerId)->count() : 0,
                'open_tasks' => $customerId && Schema::hasTable('tasks') && Schema::hasTable('projects')
                    ? DB::table('tasks')->join('projects', 'projects.id', '=', 'tasks.project_id')->where('projects.customer_id', $customerId)->whereNotIn('tasks.status', ['done', 'cancelled'])->count()
                    : 0,
                'open_tickets' => $customerId && Schema::hasTable('tickets') ? DB::table('tickets')->where('customer_id', $customerId)->where('status', '!=', 'closed')->count() : 0,
                'customers' => 1,
            ];
        }

        return [
            'customers' => Schema::hasTable('customers') ? DB::table('customers')->where('status', 'active')->count() : 0,
            'projects' => Schema::hasTable('projects') ? DB::table('projects')->whereIn('status', ['planning', 'active', 'paused'])->count() : 0,
            'open_tasks' => Schema::hasTable('tasks') ? DB::table('tasks')->whereNotIn('status', ['done', 'cancelled'])->count() : 0,
            'open_tickets' => Schema::hasTable('tickets') ? DB::table('tickets')->where('status', '!=', 'closed')->count() : 0,
        ];
    }
}
