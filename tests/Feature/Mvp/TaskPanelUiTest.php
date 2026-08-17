<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectLifecycle;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskChecklist;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Application\TaskWorkflow;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Presentation\Livewire\Form;
use Modules\Tasks\Presentation\Livewire\Index;
use Modules\Tasks\Presentation\Livewire\Show;

it('keeps task list filters and responsive task fields represented', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client, 'Panel project');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Panel task',
        'priority' => TaskPriority::High,
    ]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertSee('Panel task')
        ->assertSeeHtml('aria-label="فیلترهای سریع"')
        ->assertSee('فقط عقب‌افتاده')
        ->assertSee('زیاد')
        ->assertSee('مسئول من')
        ->assertSee('بدون مسئول')
        ->assertSeeHtml('wire:click="$set(\'overdue\', \'1\')" aria-pressed="false"')
        ->assertSeeHtml('wire:click="$set(\'assignee\', \'unassigned\')" aria-pressed="false"')
        ->assertSeeHtml('wire:click="$set(\'assignee\', \'unassigned\')"')
        ->assertSeeHtml('wire:loading.class="opacity-60" wire:target="q,project,status,priority,assignee,overdue,sort"')
        ->assertSeeHtml('class="space-y-3 lg:hidden"')
        ->assertSeeHtml('wire:key="task-card-'.$task->id.'"')
        ->assertSeeHtml('aria-pressed="false"')
        ->assertSeeHtml('min-h-11')
        ->assertSeeHtml('<details class="mobile-filter-details group">')
        ->assertSeeHtml('wire:model.live.debounce.300ms="q"')
        ->assertSeeHtml('wire:model.live="project"')
        ->assertSeeHtml('wire:model.live="status"')
        ->assertSeeHtml('wire:model.live="priority"')
        ->assertSeeHtml('wire:model.live="assignee"')
        ->assertSeeHtml('wire:model.live="overdue"')
        ->assertSeeHtml('wire:model.live="sort"')
        ->assertSee($task->reference)
        ->assertSee('Panel project')
        ->set('assignee', (string) $admin->id)
        ->assertDontSee('Panel task')
        ->assertSeeHtml('wire:click="$set(\'assignee\', \''.$admin->id.'\')" aria-pressed="true"')
        ->set('assignee', 'unassigned')
        ->assertSee('Panel task')
        ->assertSeeHtml('wire:click="$set(\'assignee\', \'unassigned\')" aria-pressed="true"');
});

it('does not expose task detail to an unauthorized user', function (): void {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $outsider = User::factory()->customer($otherClient)->create();
    $project = mvpProject($client, 'Unauthorized detail project');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Protected detail task']);

    $this->actingAs($outsider)
        ->get(route('tasks.show', $task))
        ->assertNotFound();
});

it('keeps task detail read only when its project is completed', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Completed detail project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Completed project task']);
    app(TaskWorkflow::class)->changeStatus($admin, $task, mvpDoneStatus($project));
    app(ProjectLifecycle::class)->complete($project, $admin);

    Livewire::actingAs($customer)
        ->test(Show::class, ['task' => $task->reference])
        ->assertSee('Completed project task')
        ->assertSee('چک‌لیست در تسک Done یا پروژه تکمیل‌شده فقط خواندنی است.')
        ->assertSee('این تسک یا پروژه بسته است و همکاری جدید پذیرفته نمی‌شود.')
        ->assertDontSeeHtml('wire:submit="addComment"')
        ->assertDontSeeHtml('wire:submit="addSubtask"')
        ->assertDontSeeHtml('wire:click="toggleSubtask(');
});

it('loads project task statuses in the task create form', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client);
    $status = mvpOpenStatus($project, 1);

    $this->actingAs($admin);

    Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->assertSee($status->title)
        ->assertSeeHtml('wire:model="project_status_id"');
});

it('hides admin-only task create controls from customers', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $customer, User::query()->admins()->firstOrFail());

    $this->actingAs($customer);

    Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->assertDontSeeHtml('name="priority"')
        ->assertDontSeeHtml('name="assigned_to"')
        ->assertDontSeeHtml('name="due_date"')
        ->assertSee('تسک مشتری در ریشه پروژه ایجاد می‌شود');
});

it('presents task detail as a conversation-first workspace with actionable properties', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject($client, 'Detail project');
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Detail task',
        'description' => 'A useful task description.',
        'priority' => TaskPriority::High,
    ]);
    app(TaskChecklist::class)->add($admin, $task, 'Review the result');
    app(TaskCollaboration::class)->comment($admin, $task, 'A useful conversation.', []);
    app(TaskCollaboration::class)->attach($admin, $task, UploadedFile::fake()->create('detail.pdf', 10, 'application/pdf'));

    Livewire::actingAs($admin)
        ->test(Show::class, ['task' => $task->reference])
        ->assertSee('Detail task')
        ->assertSee('A useful task description.')
        ->assertSee('Review the result')
        ->assertSee('A useful conversation.')
        ->assertSee('detail.pdf')
        ->assertSee('مشخصات تسک')
        ->assertSee('وضعیت')
        ->assertSee('مسئول')
        ->assertSee('اولویت')
        ->assertSee('موعد')
        ->assertSee('گروه کاری')
        ->assertSee('ایجادکننده')
        ->assertSee('عملیات تسک')
        ->assertSee('تاریخچه فعالیت')
        ->assertSeeHtml('<details open class="group">')
        ->assertSeeHtml('wire:submit="addComment"')
        ->assertSeeHtml('wire:submit="addSubtask"')
        ->assertSeeHtml('wire:loading wire:target="uploads"')
        ->assertSeeHtml('wire:click="hideComment(')
        ->assertSeeHtml('wire:click="hideAttachment(');
});

it('keeps task detail collaboration available to members without admin moderation controls', function (): void {
    Storage::fake('local');
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Member detail project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, ['title' => 'Member detail task']);
    app(TaskCollaboration::class)->comment($admin, $task, 'Visible conversation.', []);
    app(TaskCollaboration::class)->attach($admin, $task, UploadedFile::fake()->create('member-detail.pdf', 10, 'application/pdf'));

    Livewire::actingAs($customer)
        ->test(Show::class, ['task' => $task->reference])
        ->assertSee('Visible conversation.')
        ->assertSee('member-detail.pdf')
        ->assertSeeHtml('wire:submit="addComment"')
        ->assertSeeHtml('wire:submit="addSubtask"')
        ->assertDontSeeHtml('wire:click="hideComment(')
        ->assertDontSeeHtml('wire:click="hideAttachment(')
        ->assertDontSee('ویرایش تسک');
});

it('keeps completed task detail read only and hides admin moderation controls', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $customer = User::factory()->customer($client)->create();
    $project = mvpProject($client, 'Read only project');
    app(ProjectMembershipManager::class)->add($project, $customer, $admin);
    $task = app(TaskWorkflow::class)->createForAdmin($admin, $project, [
        'title' => 'Read only task',
        'project_status_id' => mvpDoneStatus($project)->id,
        'priority' => TaskPriority::Normal,
    ]);
    app(TaskChecklist::class)->add($admin, $task, 'Completed step');

    Livewire::actingAs($customer)
        ->test(Show::class, ['task' => $task->reference])
        ->assertSee('Completed step')
        ->assertSee('چک‌لیست در تسک Done یا پروژه تکمیل‌شده فقط خواندنی است.')
        ->assertSee('این تسک یا پروژه بسته است و همکاری جدید پذیرفته نمی‌شود.')
        ->assertDontSeeHtml('wire:submit="addComment"')
        ->assertDontSeeHtml('wire:submit="addSubtask"')
        ->assertDontSeeHtml('wire:click="toggleSubtask(')
        ->assertDontSeeHtml('wire:click="hideComment(')
        ->assertDontSeeHtml('wire:click="hideAttachment(')
        ->assertDontSeeHtml('ویرایش تسک');
});
