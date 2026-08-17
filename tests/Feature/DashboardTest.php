<?php

use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;

it('redirects authenticated users from home to dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

it('shows the generic project workflow overview to admin', function (): void {
    $admin = User::query()->admins()->firstOrFail();
    $client = Client::factory()->create(['name' => 'Admin dashboard client']);
    $project = mvpProject($client, 'Admin dashboard project');
    $openStatus = mvpOpenStatus($project);
    $dueDate = today()->addDay();

    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Admin dashboard task',
        'project_status_id' => $openStatus->id,
        'priority' => TaskPriority::Normal,
        'due_date' => $dueDate,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('داشبورد')
        ->assertSee('مشتریان فعال')
        ->assertSee('پروژه‌های فعال')
        ->assertSee('تسک‌های باز')
        ->assertSee('صف بدون مسئول')
        ->assertSee('عقب‌افتاده')
        ->assertSee('تمرکز امروز')
        ->assertSee('تسک‌های اولویت‌دار')
        ->assertSee('پروژه‌های فعال')
        ->assertSee('فعالیت‌های اخیر')
        ->assertSee(route('tasks.index', ['assignee' => 'unassigned']), false)
        ->assertSee(route('tasks.index', ['overdue' => 1]), false)
        ->assertSee(route('tasks.show', $task), false)
        ->assertSee(route('projects.show', $project), false)
        ->assertDontSee('صف ادمین')
        ->assertDontSee('منتظر مشتری')
        ->assertSee($task->reference)
        ->assertSee('Admin dashboard task')
        ->assertSee('Admin dashboard project')
        ->assertSee($openStatus->title)
        ->assertSee('نیازمند تعیین مسئول')
        ->assertSee('اولویت: عادی')
        ->assertSee('موعد: '.$dueDate->format('Y/m/d'))
        ->assertSee('بروزرسانی '.$project->updated_at->format('Y/m/d'))
        ->assertSee('تسک ایجاد شد')
        ->assertSee('dashboard-activity-heading', false)
        ->assertSee('فعال');
});

it('limits customer dashboard projects and tasks to active memberships', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();

    $visibleProject = mvpProject($client, 'Visible dashboard project');
    $hiddenProject = mvpProject($client, 'Hidden dashboard project');
    app(ProjectMembershipManager::class)->add($visibleProject, $customer, $admin);

    app(TaskWorkflow::class)->createForAdmin($admin, $visibleProject, [
        'title' => 'Visible dashboard task',
        'priority' => TaskPriority::Normal,
    ]);

    app(TaskWorkflow::class)->createForAdmin($admin, $hiddenProject, [
        'title' => 'Hidden dashboard task',
        'project_status_id' => mvpDoneStatus($hiddenProject)->id,
        'priority' => TaskPriority::Normal,
    ]);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('پروژه‌های فعال من')
        ->assertSee('تسک‌های باز')
        ->assertSee('واگذار شده به من')
        ->assertSee('عقب‌افتاده')
        ->assertSee('تمرکز امروز')
        ->assertSee('تسک‌های اولویت‌دار')
        ->assertSee('پروژه‌های فعال')
        ->assertSee('فعالیت‌های اخیر')
        ->assertSee(route('tasks.index', ['assignee' => $customer->id]), false)
        ->assertSee(route('tasks.index', ['overdue' => 1]), false)
        ->assertSee(route('tasks.show', $visibleProject->tasks()->firstOrFail()), false)
        ->assertSee(route('projects.show', $visibleProject), false)
        ->assertSee('Visible dashboard project')
        ->assertSee('Visible dashboard task')
        ->assertDontSee('Hidden dashboard project')
        ->assertDontSee('Hidden dashboard task');
});

it('shows actionable empty states for a customer without dashboard work', function (): void {
    $customer = User::factory()->customer(Client::factory()->create())->create();

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('تسکی برای نمایش نیست')
        ->assertSee('رفتن به تسک‌ها')
        ->assertSee('پروژه‌ای برای نمایش نیست')
        ->assertSee('رفتن به پروژه‌ها');
});

it('shows actionable empty states for an admin without dashboard work', function (): void {
    $admin = User::query()->admins()->firstOrFail();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('تسکی برای نمایش نیست')
        ->assertSee('پروژه‌ای برای نمایش نیست')
        ->assertSee(route('tasks.index'), false)
        ->assertSee(route('projects.index'), false)
        ->assertSee(route('tasks.index', ['assignee' => 'unassigned']), false)
        ->assertSee('مشاهده صف بدون مسئول');
});
