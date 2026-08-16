<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $creatorId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        DB::table('projects')->orderBy('id')->get(['id'])->each(function (object $project) use ($creatorId): void {
            $openId = DB::table('project_task_statuses')->insertGetId([
                'project_id' => $project->id,
                'title' => 'باز',
                'position' => 10,
                'is_done' => false,
                'is_active' => true,
                'created_by' => $creatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inProgressId = DB::table('project_task_statuses')->insertGetId([
                'project_id' => $project->id,
                'title' => 'در حال انجام',
                'position' => 20,
                'is_done' => false,
                'is_active' => true,
                'created_by' => $creatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $doneId = DB::table('project_task_statuses')->insertGetId([
                'project_id' => $project->id,
                'title' => 'انجام‌شده',
                'position' => 30,
                'is_done' => true,
                'is_active' => true,
                'created_by' => $creatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tasks')
                ->where('project_id', $project->id)
                ->orderBy('id')
                ->get(['id', 'status', 'completed_at', 'updated_at'])
                ->each(function (object $task) use ($openId, $inProgressId, $doneId): void {
                    $isDone = $task->status === 'completed' || $task->completed_at !== null;
                    $statusId = $isDone
                        ? $doneId
                        : ($task->status === 'in_progress' ? $inProgressId : $openId);

                    $updates = ['project_status_id' => $statusId];
                    if ($task->status === 'completed' && $task->completed_at === null) {
                        $updates['completed_at'] = $task->updated_at ?? now();
                    }

                    DB::table('tasks')->where('id', $task->id)->update($updates);
                });
        });
    }

    public function down(): void
    {
        // The following schema rollback reconstructs legacy task status values.
    }
};
