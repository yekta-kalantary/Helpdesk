<?php

namespace Modules\Tasks\Application;

use App\Support\ActivityRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskChecklistItem;

class TaskChecklist
{
    public function __construct(private readonly ActivityRecorder $activities) {}

    public function add(User $actor, Task $task, string $title): TaskChecklistItem
    {
        $title = $this->title($title);

        return DB::transaction(function () use ($actor, $task, $title): TaskChecklistItem {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertMutable($actor, $task);
            $position = ((int) $task->checklistItems()->max('position')) + 10;

            $item = TaskChecklistItem::query()->create([
                'task_id' => $task->id,
                'title' => $title,
                'is_completed' => false,
                'position' => $position,
                'created_by' => $actor->id,
            ]);

            $this->activities->record($actor, 'subtask.added', $task->project, $task, [
                'subtask_id' => $item->id,
                'title' => $item->title,
            ]);

            return $item;
        });
    }

    public function rename(User $actor, TaskChecklistItem $item, string $title): TaskChecklistItem
    {
        $title = $this->title($title);

        return DB::transaction(function () use ($actor, $item, $title): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actor, $task);
            $old = $item->title;

            if ($old !== $title) {
                $item->update(['title' => $title]);
                $this->activities->record($actor, 'subtask.renamed', $task->project, $task, [
                    'subtask_id' => $item->id,
                    'old_title' => $old,
                    'new_title' => $title,
                ]);
            }

            return $item->refresh();
        });
    }

    public function toggle(User $actor, TaskChecklistItem $item, bool $completed): TaskChecklistItem
    {
        return DB::transaction(function () use ($actor, $item, $completed): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actor, $task);

            if ($item->is_completed === $completed) {
                return $item;
            }

            $item->update(['is_completed' => $completed]);
            $this->activities->record(
                $actor,
                $completed ? 'subtask.completed' : 'subtask.uncompleted',
                $task->project,
                $task,
                ['subtask_id' => $item->id, 'title_snapshot' => $item->title],
            );

            return $item->refresh();
        });
    }

    /** @param array<int, int> $orderedItemIds */
    public function reorder(User $actor, Task $task, array $orderedItemIds): void
    {
        DB::transaction(function () use ($actor, $task, $orderedItemIds): void {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertMutable($actor, $task);
            $expected = $task->checklistItems()->pluck('id')->sort()->values()->all();
            $actual = collect($orderedItemIds)->map(fn ($id): int => (int) $id)->sort()->values()->all();

            if ($actual !== $expected || count($orderedItemIds) !== count(array_unique($orderedItemIds))) {
                throw new DomainException('Checklist order must contain every active Subtask exactly once.');
            }

            foreach ($orderedItemIds as $index => $itemId) {
                TaskChecklistItem::query()
                    ->where('task_id', $task->id)
                    ->whereNull('removed_at')
                    ->whereKey((int) $itemId)
                    ->update(['position' => ($index + 1) * 10]);
            }
        });
    }

    public function remove(User $actor, TaskChecklistItem $item): TaskChecklistItem
    {
        return DB::transaction(function () use ($actor, $item): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actor, $task);

            $item->update(['removed_at' => now()]);
            $this->activities->record($actor, 'subtask.removed', $task->project, $task, [
                'subtask_id' => $item->id,
                'title_snapshot' => $item->title,
            ]);

            return $item->refresh();
        });
    }

    private function assertMutable(User $actor, Task $task): void
    {
        if (! $actor->is_active || ! $actor->canAuthenticate()) {
            throw new DomainException('An active account is required.');
        }

        if (! $actor->isAdmin() && ! Task::query()->visibleTo($actor)->whereKey($task->id)->exists()) {
            throw new DomainException('Task access is not allowed.');
        }

        $task->loadMissing(['project.client', 'projectStatus']);
        if (! $task->project->isActive() || ! $task->project->client->isActive() || $task->isDone()) {
            throw new DomainException('Done Tasks and completed Projects have read-only checklists.');
        }
    }

    private function assertActiveItem(TaskChecklistItem $item): void
    {
        if ($item->isRemoved()) {
            throw new DomainException('Removed Subtasks are read-only.');
        }
    }

    private function title(string $title): string
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 255) {
            throw new DomainException('Subtask title is required and may not exceed 255 characters.');
        }

        return $title;
    }
}
