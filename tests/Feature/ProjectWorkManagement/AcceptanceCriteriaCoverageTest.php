<?php

use App\Models\Activity;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Projects\Application\ProjectWorkflowManager;
use Modules\Projects\Application\WorkGroupManager;
use Modules\Projects\Presentation\Livewire\Show as ProjectShow;
use Modules\Tasks\Application\TaskChecklist;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('keeps root and level-five task placement valid while rejecting cross-project Work Groups and auditing moves', function (): void {
    $client = Client::factory()->create();
    $project = mvpProject($client, 'Hierarchy A');
    $otherProject = mvpProject($client, 'Hierarchy B');
    $admin = User::factory()->admin()->create();
    $groups = app(WorkGroupManager::class);
    $workflow = app(TaskWorkflow::class);

    $parent = null;
    foreach (range(1, 5) as $level) {
        $parent = $groups->create($admin, $project, [
            'title' => "Level {$level}",
            'parent_id' => $parent?->id,
        ]);
    }
    $levelFive = $parent;
    $foreign = $groups->create($admin, $otherProject, ['title' => 'Foreign group']);

    $rootTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Root task',
        'priority' => TaskPriority::Normal,
    ]);
    $deepTask = $workflow->createForAdmin($admin, $project, [
        'title' => 'Level five task',
        'work_group_id' => $levelFive->id,
        'priority' => TaskPriority::Normal,
    ]);

    expect($rootTask->work_group_id)->toBeNull()
        ->and($deepTask->work_group_id)->toBe($levelFive->id)
        ->and(fn () => $groups->create($admin, $project, [
            'title' => 'Foreign parent child',
            'parent_id' => $foreign->id,
        ]))->toThrow(DomainException::class)
        ->and(fn () => $workflow->createForAdmin($admin, $project, [
            'title' => 'Foreign grouped task',
            'work_group_id' => $foreign->id,
            'priority' => TaskPriority::Normal,
        ]))->toThrow(DomainException::class);

    $deepTask = $workflow->updateByAdmin($admin, $deepTask, ['work_group_id' => null]);
    $activity = Activity::query()
        ->where('task_id', $deepTask->id)
        ->where('action', 'task.work_group_changed')
        ->latest('id')
        ->firstOrFail();

    expect($deepTask->work_group_id)->toBeNull()
        ->and($activity->actor_id)->toBe($admin->id)
        ->and($activity->metadata['old_work_group_id'])->toBe($levelFive->id)
        ->and($activity->metadata['new_work_group_id'])->toBeNull();
});

it('keeps a project without Work Groups first-class and avoids rendering fake hierarchy', function (): void {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Simple project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Simple root task',
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer);

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Simple root task')
        ->assertSee('کانبان پروژه')
        ->assertDontSee('Root Tasks')
        ->assertDontSee('نمای Hierarchy');
});

it('derives Work Group progress only from Task Done state and never directly from Subtask completion', function (): void {
    $project = mvpProject(Client::factory()->create(), 'Progress project');
    $admin = User::factory()->admin()->create();
    $group = app(WorkGroupManager::class)->create($admin, $project, ['title' => 'Progress group']);
    $workflow = app(TaskWorkflow::class);
    $checklist = app(TaskChecklist::class);
    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Progress parent',
        'work_group_id' => $group->id,
        'priority' => TaskPriority::Normal,
    ]);

    $first = $checklist->add($admin, $task, 'Step one');
    $second = $checklist->add($admin, $task, 'Step two');
    $checklist->toggle($admin, $first, true);
    $checklist->toggle($admin, $second, true);

    expect($task->refresh()->isDone())->toBeFalse();

    $this->actingAs($admin);
    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Progress: 0/1 · 0%');

    $workflow->changeStatus($admin, $task, mvpDoneStatus($project));

    Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Progress: 1/1 · 100%');
});

it('reorders project statuses, renders active columns in that order, and rejects inactivation of a populated status', function (): void {
    $project = mvpProject(Client::factory()->create(), 'Workflow ordering');
    $admin = User::factory()->admin()->create();
    $manager = app(ProjectWorkflowManager::class);
    $workflow = app(TaskWorkflow::class);
    $review = $manager->create($admin, $project, 'Review first');
    $unused = $manager->create($admin, $project, 'Unused status');

    $remainingIds = $project->taskStatuses()->active()->where('id', '!=', $review->id)->orderBy('position')->pluck('id')->all();
    $manager->reorder($admin, $project, [$review->id, ...$remainingIds]);

    $task = $workflow->createForAdmin($admin, $project, [
        'title' => 'Pinned review task',
        'project_status_id' => $review->id,
        'priority' => TaskPriority::Normal,
    ]);

    expect($review->refresh()->position)->toBe(10)
        ->and(fn () => $manager->inactivate($admin, $review))->toThrow(DomainException::class);

    $manager->inactivate($admin, $unused);

    $this->actingAs($admin);
    $component = Livewire::test(ProjectShow::class, ['project' => $project->id])
        ->assertSee('Review first')
        ->assertSee('Pinned review task')
        ->assertDontSee('Unused status');
    $html = $component->html();

    expect(strpos($html, 'Review first'))->toBeLessThan(strpos($html, 'باز'))
        ->and($task->refresh()->project_status_id)->toBe($review->id);
});
