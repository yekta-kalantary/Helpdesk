<?php

namespace App\Support;

use App\Models\Activity;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

class ActivityRecorder
{
    public function record(
        ?User $actor,
        string $action,
        ?Project $project = null,
        ?Task $task = null,
        array $metadata = [],
    ): Activity {
        return $this->recordIds($actor?->id, $action, $project?->id ?? $task?->project_id, $task?->id, $metadata);
    }

    public function recordIds(?int $actorId, string $action, ?int $projectId, ?int $taskId, array $metadata = []): Activity
    {
        return Activity::query()->create([
            'actor_id' => $actorId,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'action' => $action,
            'metadata' => $this->sanitize($metadata),
            'created_at' => now(),
        ]);
    }

    private function sanitize(array $metadata): array
    {
        $safe = [];

        foreach ($metadata as $key => $value) {
            $name = strtolower((string) $key);

            if (str_contains($name, 'password')
                || str_contains($name, 'token')
                || str_contains($name, 'secret')
                || str_contains($name, 'credential')) {
                continue;
            }

            $safe[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $safe;
    }
}
