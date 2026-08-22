<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Application\ProjectCreator;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Infrastructure\Models\ProjectTaskStatus;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);
    })
    ->in('Feature');

function mvpProject(Client $client, string $name = 'Project'): Project
{
    $creatorId = User::query()->active()->admins()->orderBy('id')->value('id');

    return app(ProjectCreator::class)->create($creatorId, [
        'client_id' => $client->id,
        'name' => $name,
        'status' => ProjectStatus::Active,
    ]);
}

function mvpOpenStatus(Project $project, int $offset = 0): ProjectTaskStatus
{
    return $project->taskStatuses()
        ->active()
        ->where('is_done', false)
        ->orderBy('position')
        ->orderBy('id')
        ->skip($offset)
        ->firstOrFail();
}

function mvpDoneStatus(Project $project): ProjectTaskStatus
{
    return $project->taskStatuses()
        ->active()
        ->where('is_done', true)
        ->firstOrFail();
}
