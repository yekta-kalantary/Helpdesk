<?php

use App\Models\Activity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectMembershipManager;
use Modules\Tasks\Application\TaskCollaboration;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Presentation\Livewire\Form;

it('does not create a task when its initial attachment fails validation', function (): void {
    Storage::fake('local');
    $admin = User::query()->admins()->firstOrFail();
    $project = mvpProject(Client::factory()->create());

    $this->actingAs($admin);

    Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->set('title', 'Invalid attachment')
        ->set('attachment', UploadedFile::fake()->create('payload.php', 2, 'text/x-php'))
        ->call('save')
        ->assertHasErrors('attachment');

    expect(Task::query()->where('title', 'Invalid attachment')->exists())->toBeFalse()
        ->and(Activity::query()->where('action', 'task.created')->exists())->toBeFalse()
        ->and(Attachment::query()->exists())->toBeFalse()
        ->and(DB::table('notifications')->exists())->toBeFalse()
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});

it('rolls back task creation when its initial attachment fails after task creation', function (): void {
    $client = Client::factory()->create();
    $admin = User::query()->admins()->firstOrFail();
    $member = User::factory()->customer($client)->create();
    $project = mvpProject($client);
    app(ProjectMembershipManager::class)->add($project, $member, $admin);
    $notificationCount = DB::table('notifications')->count();

    $this->mock(TaskCollaboration::class, function ($mock): void {
        $mock->shouldReceive('attach')->once()->andThrow(new RuntimeException('attachment failed'));
    });

    $this->actingAs($admin);

    expect(fn () => Livewire::test(Form::class)
        ->set('project_id', $project->id)
        ->set('title', 'Atomic task')
        ->set('attachment', UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf'))
        ->call('save'))
        ->toThrow(RuntimeException::class);

    expect(Task::query()->where('title', 'Atomic task')->exists())->toBeFalse()
        ->and(Activity::query()->where('action', 'task.created')->exists())->toBeFalse()
        ->and(Attachment::query()->exists())->toBeFalse()
        ->and(DB::table('notifications')->count())->toBe($notificationCount);
});
