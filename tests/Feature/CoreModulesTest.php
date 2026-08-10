<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

it('exposes only users projects and tasks', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();

    $this->actingAs($admin)->get(route('projects.index'))->assertOk();
    $this->get(route('tasks.index'))->assertOk();
    $this->get(route('users.index'))->assertOk();

    expect(Route::has('contacts.index'))->toBeFalse()
        ->and(Route::has('roles.index'))->toBeFalse()
        ->and(Route::has('tasks.show'))->toBeFalse()
        ->and(Route::has('tasks.attachments.download'))->toBeFalse();
});

it('keeps user profile data directly on users', function (): void {
    $user = User::factory()->create([
        'name' => 'Yekta',
        'last_name' => 'Kalantary',
        'email' => 'yekta@example.test',
        'mobile' => '09120000000',
    ]);

    expect($user->full_name)->toBe('Yekta Kalantary')
        ->and($user->email)->toBe('yekta@example.test')
        ->and($user->mobile)->toBe('09120000000');
});

it('links users to projects through project membership', function (): void {
    $user = User::factory()->create();
    $project = Project::query()->create([
        'title' => 'Example project',
        'description' => 'Simple project',
    ]);

    $project->members()->attach($user->id);

    expect($project->members()->whereKey($user->id)->exists())->toBeTrue()
        ->and($user->projects()->whereKey($project->id)->exists())->toBeTrue();
});
