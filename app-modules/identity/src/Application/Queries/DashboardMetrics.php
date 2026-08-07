<?php

namespace Modules\Identity\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetrics
{
    /** @return array<string,int> */
    public function for(User $user): array
    {
        if ($user->person?->type === PersonType::Customer) {
            $customerId = Schema::hasTable('customers')
                ? DB::table('customers')->where('person_id', $user->person_id)->whereNull('deleted_at')->value('id')
                : null;

            return [
                'projects' => $customerId && Schema::hasTable('projects')
                    ? DB::table('projects')->where('customer_id', $customerId)->whereNull('deleted_at')->count()
                    : 0,
                'open_tasks' => $customerId && Schema::hasTable('tasks') && Schema::hasTable('projects')
                    ? DB::table('tasks')
                        ->join('projects', 'projects.id', '=', 'tasks.project_id')
                        ->where('projects.customer_id', $customerId)
                        ->whereNull('projects.deleted_at')
                        ->whereNull('tasks.deleted_at')
                        ->where('tasks.is_customer_visible', true)
                        ->whereNotIn('tasks.status', ['done', 'cancelled'])
                        ->count()
                    : 0,
                'open_tickets' => $customerId && Schema::hasTable('tickets')
                    ? DB::table('tickets')->where('customer_id', $customerId)->whereNull('deleted_at')->where('status', '!=', 'closed')->count()
                    : 0,
                'customers' => 1,
            ];
        }

        return [
            'customers' => Schema::hasTable('customers') ? DB::table('customers')->whereNull('deleted_at')->where('status', 'active')->count() : 0,
            'projects' => Schema::hasTable('projects') ? DB::table('projects')->whereNull('deleted_at')->whereIn('status', ['planning', 'active', 'paused'])->count() : 0,
            'open_tasks' => Schema::hasTable('tasks') ? DB::table('tasks')->whereNull('deleted_at')->whereNotIn('status', ['done', 'cancelled'])->count() : 0,
            'open_tickets' => Schema::hasTable('tickets') ? DB::table('tickets')->whereNull('deleted_at')->where('status', '!=', 'closed')->count() : 0,
        ];
    }
}
