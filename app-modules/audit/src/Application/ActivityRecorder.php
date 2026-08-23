<?php

namespace Modules\Audit\Application;

use Modules\Audit\Infrastructure\Models\Activity;

class ActivityRecorder
{
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
