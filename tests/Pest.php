<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Infrastructure\Models\Project;
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
    return Project::query()->create([
        'client_id' => $client->id,
        'name' => $name,
        'status' => ProjectStatus::Active,
    ]);
}
