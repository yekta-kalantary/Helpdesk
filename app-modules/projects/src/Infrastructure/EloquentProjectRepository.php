<?php

namespace Modules\Projects\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Infrastructure\Models\Project;

class EloquentProjectRepository implements ProjectRepository
{
    public function search(?string $term = null, ?int $customerId = null): array
    {
        $query = DB::table('projects')
            ->leftJoin('customers', 'customers.id', '=', 'projects.customer_id')
            ->leftJoin('people as customer_people', 'customer_people.id', '=', 'customers.person_id')
            ->whereNull('projects.deleted_at')
            ->select([
                'projects.id', 'projects.customer_id', 'projects.title', 'projects.type', 'projects.status',
                'projects.starts_at', 'projects.ends_at', 'projects.created_at',
                'customer_people.first_name as customer_first_name', 'customer_people.last_name as customer_last_name',
            ]);

        if (Schema::hasTable('tasks')) {
            $tasksCount = DB::table('tasks')
                ->selectRaw('count(*)')
                ->whereColumn('tasks.project_id', 'projects.id')
                ->whereNull('tasks.deleted_at')
                ->when($customerId, fn ($builder) => $builder->where('tasks.is_customer_visible', true));

            $tasksDone = DB::table('tasks')
                ->selectRaw('count(*)')
                ->whereColumn('tasks.project_id', 'projects.id')
                ->whereNull('tasks.deleted_at')
                ->where('tasks.status', 'done')
                ->when($customerId, fn ($builder) => $builder->where('tasks.is_customer_visible', true));

            $query->addSelect([
                'tasks_count' => $tasksCount,
                'tasks_done' => $tasksDone,
            ]);
        }

        $rows = $query
            ->when($customerId, fn ($builder) => $builder->where('projects.customer_id', $customerId))
            ->when($term, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('projects.title', 'like', "%{$term}%")
                ->orWhere('customer_people.first_name', 'like', "%{$term}%")
                ->orWhere('customer_people.last_name', 'like', "%{$term}%")))
            ->orderByDesc('projects.id')
            ->get();

        return $rows->map(function (object $row): array {
            $count = (int) ($row->tasks_count ?? 0);
            $done = (int) ($row->tasks_done ?? 0);

            return [
                'id' => $row->id,
                'customer_id' => $row->customer_id,
                'customer_name' => trim($row->customer_first_name.' '.$row->customer_last_name),
                'title' => $row->title,
                'type' => $row->type,
                'status' => $row->status,
                'starts_at' => $row->starts_at,
                'ends_at' => $row->ends_at,
                'progress' => $count > 0 ? (int) round(($done / $count) * 100) : 0,
            ];
        })->all();
    }

    public function find(int $id): array
    {
        $project = Project::query()->findOrFail($id);
        $customer = DB::table('customers')
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->where('customers.id', $project->customer_id)
            ->first(['people.first_name', 'people.last_name']);

        return [
            'id' => $project->id,
            'customer_id' => $project->customer_id,
            'customer_name' => $customer ? trim($customer->first_name.' '.$customer->last_name) : null,
            'title' => $project->title,
            'type' => $project->type->value,
            'description' => $project->description,
            'status' => $project->status->value,
            'starts_at' => $project->starts_at?->format('Y-m-d'),
            'ends_at' => $project->ends_at?->format('Y-m-d'),
            'member_ids' => $project->members()->pluck('users.id')->all(),
        ];
    }

    public function create(array $attributes, array $memberIds): int
    {
        return DB::transaction(function () use ($attributes, $memberIds): int {
            $project = Project::create($attributes);
            $project->members()->sync($memberIds);

            return $project->id;
        });
    }

    public function update(int $id, array $attributes, array $memberIds): void
    {
        DB::transaction(function () use ($id, $attributes, $memberIds): void {
            $project = Project::query()->findOrFail($id);
            $project->update($attributes);
            $project->members()->sync($memberIds);
        });
    }

    public function delete(int $id): void
    {
        Project::query()->findOrFail($id)->delete();
    }
}
