<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

class CustomerAssignmentRequeuer
{
    public function __construct(private readonly ActivityRecorder $activities) {}

    public function requeue(User $customer, User $actor, ?Project $project = null): void
    {
        Task::query()
            ->where('assigned_to', $customer->id)
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->when($project, fn (Builder $query) => $query->where('project_id', $project->id))
            ->with('project')
            ->get()
            ->each(function (Task $task) use ($actor): void {
                $oldStatus = $task->status;
                $oldAssignee = $task->assigned_to;

                $task->forceFill([
                    'status' => TaskStatus::WaitingAdmin,
                    'assigned_to' => null,
                    'completed_at' => null,
                ])->save();

                if ($oldAssignee !== $task->assigned_to) {
                    $this->activities->record($actor, 'task.assignee_changed', $task->project, $task, [
                        'old' => $oldAssignee,
                        'new' => null,
                    ]);
                }

                if ($oldStatus !== $task->status) {
                    $this->activities->record($actor, 'task.status_changed', $task->project, $task, [
                        'old' => $oldStatus->value,
                        'new' => TaskStatus::WaitingAdmin->value,
                    ]);
                }
            });
    }
}
