<?php

namespace Modules\Tasks\Application;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Audit\Application\ActivityRecorder;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Infrastructure\Models\TaskChecklistItem;

class TaskChecklist
{
    public function __construct(
        private readonly ActivityRecorder $activities,
        private readonly TaskAccess $access,
    ) {}

    public function add(int $actorId, Task $task, string $title): TaskChecklistItem
    {
        $title = $this->title($title);

        return DB::transaction(function () use ($actorId, $task, $title): TaskChecklistItem {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertMutable($actorId, $task);
            $position = ((int) $task->checklistItems()->max('position')) + 10;

            $item = TaskChecklistItem::query()->create([
                'task_id' => $task->id,
                'title' => $title,
                'is_completed' => false,
                'position' => $position,
                'created_by' => $actorId,
            ]);

            $this->activities->recordIds($actorId, 'subtask.added', (int) $task->project_id, $task->id, [
                'subtask_id' => $item->id,
                'title' => $item->title,
            ]);

            return $item;
        });
    }

    public function rename(int $actorId, TaskChecklistItem $item, string $title): TaskChecklistItem
    {
        $title = $this->title($title);

        return DB::transaction(function () use ($actorId, $item, $title): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actorId, $task);
            $old = $item->title;

            if ($old !== $title) {
                $item->update(['title' => $title]);
                $this->activities->recordIds($actorId, 'subtask.renamed', (int) $task->project_id, $task->id, [
                    'subtask_id' => $item->id,
                    'old_title' => $old,
                    'new_title' => $title,
                ]);
            }

            return $item->refresh();
        });
    }

    public function toggle(int $actorId, TaskChecklistItem $item, bool $completed): TaskChecklistItem
    {
        return DB::transaction(function () use ($actorId, $item, $completed): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actorId, $task);

            if ($item->is_completed === $completed) {
                return $item;
            }

            $item->update(['is_completed' => $completed]);
            $this->activities->recordIds(
                $actorId,
                $completed ? 'subtask.completed' : 'subtask.uncompleted',
                (int) $task->project_id,
                $task->id,
                ['subtask_id' => $item->id, 'title_snapshot' => $item->title],
            );

            return $item->refresh();
        });
    }

    /** @param array<int, int> $orderedItemIds */
    public function reorder(int $actorId, Task $task, array $orderedItemIds): void
    {
        DB::transaction(function () use ($actorId, $task, $orderedItemIds): void {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertMutable($actorId, $task);
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

    public function remove(int $actorId, TaskChecklistItem $item): TaskChecklistItem
    {
        return DB::transaction(function () use ($actorId, $item): TaskChecklistItem {
            $item = TaskChecklistItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->assertActiveItem($item);
            $task = Task::query()->lockForUpdate()->findOrFail($item->task_id);
            $this->assertMutable($actorId, $task);

            $item->update(['removed_at' => now()]);
            $this->activities->recordIds($actorId, 'subtask.removed', (int) $task->project_id, $task->id, [
                'subtask_id' => $item->id,
                'title_snapshot' => $item->title,
            ]);

            return $item->refresh();
        });
    }

    private function assertMutable(int $actorId, Task $task): void
    {
        $this->access->assertMutable($actorId, $task);
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
