<?php

use App\Models\Activity;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Route;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskChecklist;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Infrastructure\Models\Task;

beforeEach(function (): void {
    Route::get('/tasks/{task}', fn (): string => '')->name('tasks.show');
    Route::getRoutes()->refreshNameLookups();
});

it('allows employees to create and transition tasks only in member projects', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee()->create();
    $project = mvpProject($client, 'Employee task project');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $employee->id, $admin->id);
    $workflow = app(TaskWorkflow::class);

    $task = $workflow->createForEmployee($employee, $project, ['title' => 'Employee task']);

    expect($task->created_by)->toBe($employee->id);

    $workflow->transitionByEmployee($employee, $task, mvpDoneStatus($project));

    expect($task->refresh()->completed_at)->not->toBeNull();
});

it('allows clientless active employee members as task assignees and rejects removed members', function (): void {
    $clientA = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employeeA = User::factory()->employee()->create();
    $employeeB = User::factory()->employee()->create();
    $project = mvpProject($clientA, 'Assignment project');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $employeeA->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $employeeA->id,
        'title' => 'Assigned task',
    ]);

    expect($task->assigned_to)->toBe($employeeA->id);

    $manager->remove($project, $employeeA->id, $admin->id);
    expect(fn () => app(TaskWorkflow::class)->updateByAdmin($admin, $task, ['assigned_to' => $employeeA->id]))->toThrow(DomainException::class)
        ->and(fn () => app(TaskWorkflow::class)->updateByAdmin($admin, $task, ['assigned_to' => $employeeB->id]))->toThrow(DomainException::class);
});

it('keeps employee task mutations separate from admin-only operations', function (): void {
    $client = Client::factory()->create();
    $employee = User::factory()->employee($client)->create();
    $project = mvpProject($client, 'Admin-only task project');
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $employee->id,
        'title' => 'Task',
    ]);

    expect(fn () => app(TaskWorkflow::class)->updateByAdmin($employee, $task, ['title' => 'Changed']))
        ->toThrow(DomainException::class)
        ->and(fn () => app(ProjectMembershipManager::class)->add($project, $employee->id, $employee->id))
        ->toThrow(DomainException::class);
});

it('records task lifecycle activity through the audit path', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee()->create();
    $project = mvpProject($client, 'Audited task project');
    app(ProjectMembershipManager::class)->add($project, $employee->id, $admin->id);
    $workflow = app(TaskWorkflow::class);

    $task = $workflow->createForEmployee($employee, $project, ['title' => 'Audited task']);
    $workflow->transitionByEmployee($employee, $task, mvpDoneStatus($project));

    expect(Activity::query()->where('action', 'task.created')->where('task_id', $task->id)->exists())->toBeTrue()
        ->and(Activity::query()->where('action', 'task.status_changed')->where('task_id', $task->id)->exists())->toBeTrue()
        ->and(Activity::query()->where('action', 'task.completed')->where('task_id', $task->id)->exists())->toBeTrue();
});

it('authorizes task views through project membership', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $outsider = User::factory()->employee()->create();
    $project = mvpProject($client, 'Policy task project');
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'title' => 'Policy task',
    ]);
    $policy = app(TaskPolicy::class);

    expect($policy->view($member, $task))->toBeTrue()
        ->and($policy->view($outsider, $task))->toBeFalse()
        ->and($policy->view($admin, $task))->toBeTrue();
});

it('guards checklist mutations with project membership and task state', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create();
    $outsider = User::factory()->employee()->create();
    $project = mvpProject($client, 'Checklist task project');
    app(ProjectMembershipManager::class)->add($project, $member->id, $admin->id);
    $doneStatus = mvpDoneStatus($project);
    $openTask = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'title' => 'Open checklist task',
    ]);
    $doneTask = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => $doneStatus->id,
        'created_by' => $admin->id,
        'completed_at' => now(),
        'title' => 'Done checklist task',
    ]);
    $checklist = app(TaskChecklist::class);

    $item = $checklist->add($member->id, $openTask, 'First subtask');

    expect($item->task_id)->toBe($openTask->id)
        ->and(fn () => $checklist->add($outsider->id, $openTask, 'No access'))->toThrow(DomainException::class)
        ->and(fn () => $checklist->add($member->id, $doneTask, 'Read only'))->toThrow(DomainException::class);
});
