<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Infrastructure\Models\Task;

beforeEach(function (): void {
    Route::get('/tasks/{task}', fn (): string => '')->name('tasks.show');
    Route::getRoutes()->refreshNameLookups();
});

it('allows employees to create and transition tasks only in member projects', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee($client)->create();
    $project = mvpProject($client, 'Employee task project');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $employee, $admin);
    $workflow = app(TaskWorkflow::class);

    $task = $workflow->createForEmployee($employee, $project, ['title' => 'Employee task']);

    expect($task->created_by)->toBe($employee->id);

    $workflow->transitionByEmployee($employee, $task, mvpDoneStatus($project));

    expect($task->refresh()->isDone())->toBeTrue();
});

it('allows active employee members as task assignees and rejects removed or cross-client members', function (): void {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $employeeA = User::factory()->employee($clientA)->create();
    $employeeB = User::factory()->employee($clientB)->create();
    $project = mvpProject($clientA, 'Assignment project');
    $manager = app(ProjectMembershipManager::class);
    $manager->add($project, $employeeA, $admin);
    $task = Task::query()->create([
        'project_id' => $project->id,
        'project_status_id' => mvpOpenStatus($project)->id,
        'created_by' => $admin->id,
        'assigned_to' => $employeeA->id,
        'title' => 'Assigned task',
    ]);

    expect($task->assigned_to)->toBe($employeeA->id);

    $manager->remove($project, $employeeA, $admin);
    $task->update(['assigned_to' => null]);

    expect(fn () => $task->update(['assigned_to' => $employeeA->id]))->toThrow(DomainException::class)
        ->and(fn () => $task->update(['assigned_to' => $employeeB->id]))->toThrow(DomainException::class);
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
        ->and(fn () => app(ProjectMembershipManager::class)->add($project, $employee, $employee))
        ->toThrow(DomainException::class);
});
