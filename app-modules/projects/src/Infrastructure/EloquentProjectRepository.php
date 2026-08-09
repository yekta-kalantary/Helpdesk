<?php

namespace Modules\Projects\Infrastructure;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Infrastructure\Models\Project;

class EloquentProjectRepository implements ProjectRepository
{
    public function search(?string $term = null, ?int $contactId = null): array
    {
        $query = DB::table('projects')
            ->leftJoin('contacts', 'contacts.id', '=', 'projects.contact_id')
            ->whereNull('projects.deleted_at')
            ->select([
                'projects.id', 'projects.contact_id', 'projects.category', 'projects.title', 'projects.type', 'projects.status',
                'projects.starts_at', 'projects.ends_at', 'projects.created_at',
                'contacts.first_name as contact_first_name', 'contacts.last_name as contact_last_name',
            ]);

        if (Schema::hasTable('tasks')) {
            $tasksCount = DB::table('tasks')
                ->selectRaw('count(*)')
                ->whereColumn('tasks.project_id', 'projects.id')
                ->whereNull('tasks.deleted_at');

            $tasksDone = DB::table('tasks')
                ->selectRaw('count(*)')
                ->whereColumn('tasks.project_id', 'projects.id')
                ->whereNull('tasks.deleted_at')
                ->where('tasks.status', 'done');

            $query->addSelect([
                'tasks_count' => $tasksCount,
                'tasks_done' => $tasksDone,
            ]);
        }

        $rows = $query
            ->when($contactId, fn ($builder) => $builder->where('projects.contact_id', $contactId))
            ->when($term, fn ($builder) => $builder->where(fn ($nested) => $nested
                ->where('projects.title', 'like', "%{$term}%")
                ->orWhere('contacts.first_name', 'like', "%{$term}%")
                ->orWhere('contacts.last_name', 'like', "%{$term}%")))
            ->orderByDesc('projects.id')
            ->get();

        return $rows->map(function (object $row): array {
            $count = (int) ($row->tasks_count ?? 0);
            $done = (int) ($row->tasks_done ?? 0);
            $contactName = trim(($row->contact_first_name ?? '').' '.($row->contact_last_name ?? ''));

            return [
                'id' => $row->id,
                'contact_id' => $row->contact_id,
                'contact_name' => $contactName !== '' ? $contactName : null,
                'category' => $row->category,
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
        $project = Project::query()->with('contact')->findOrFail($id);

        return [
            'id' => $project->id,
            'contact_id' => $project->contact_id,
            'contact_name' => $project->contact?->full_name,
            'category' => $project->category->value,
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
