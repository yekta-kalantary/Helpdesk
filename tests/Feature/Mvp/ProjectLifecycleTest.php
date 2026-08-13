<?php

use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Modules\Tasks\Infrastructure\Models\Task;

it('rejects project completion while a non terminal task exists', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Open task',
        'status' => TaskStatus::WaitingAdmin,
        'priority' => TaskPriority::Normal,
    ]);

    expect(fn () => app(ProjectLifecycle::class)->complete($project, $admin))
        ->toThrow(DomainException::class);
});

it('allows completion when every task is terminal and lets admin reopen', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);

    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Done',
        'status' => TaskStatus::Completed,
        'priority' => TaskPriority::Normal,
    ]);
    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Cancelled',
        'status' => TaskStatus::Cancelled,
        'priority' => TaskPriority::Normal,
    ]);

    app(ProjectLifecycle::class)->complete($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Completed);

    app(ProjectLifecycle::class)->reopen($project, $admin);
    expect($project->refresh()->status)->toBe(ProjectStatus::Active)
        ->and(Activity::query()->where('project_id', $project->id)->where('action', 'project.status_changed')->count())->toBe(2);
});

it('does not complete a project while a concurrent open task transaction is pending', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $project = mvpProject($client);
    $resultPath = tempnam(sys_get_temp_dir(), 'project-lifecycle-');

    DB::commit();

    try {
        DB::beginTransaction();
        $task = Task::query()->create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'Concurrent open task',
            'status' => TaskStatus::WaitingAdmin,
            'priority' => TaskPriority::Normal,
        ]);
        Project::query()->whereKey($project)->lockForUpdate()->firstOrFail();

        $child = pcntl_fork();
        expect($child)->not->toBe(-1);

        if ($child === 0) {
            DB::disconnect();
            DB::reconnect();

            try {
                app(ProjectLifecycle::class)->complete(Project::query()->findOrFail($project->id), $admin);
                file_put_contents($resultPath, 'completed');
            } catch (DomainException) {
                file_put_contents($resultPath, 'rejected');
            } finally {
                exit(0);
            }
        }

        usleep(100000);
        DB::commit();
        pcntl_waitpid($child, $status);

        expect(file_get_contents($resultPath))->toBe('rejected')
            ->and($task->refresh()->status)->toBe(TaskStatus::WaitingAdmin)
            ->and($project->refresh()->status)->toBe(ProjectStatus::Active);
    } finally {
        if (DB::connection()->transactionLevel() > 0) {
            DB::rollBack();
        }

        Activity::query()->where('project_id', $project->id)->delete();
        Task::query()->whereKey($task->id)->delete();
        $project->delete();
        $admin->delete();
        $client->delete();
        @unlink($resultPath);
        DB::beginTransaction();
    }
});
