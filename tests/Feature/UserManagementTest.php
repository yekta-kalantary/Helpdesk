<?php

use Modules\Identity\Domain\Contracts\UserRepository;
use Modules\Identity\Infrastructure\Models\User;

it('keeps the seeded admin separate from normal users', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $normal = User::factory()->create();

    $listedIds = collect(app(UserRepository::class)->search())->pluck('id')->all();

    expect($admin->is_admin)->toBeTrue()
        ->and($normal->is_admin)->toBeFalse()
        ->and($listedIds)->toContain($normal->id)
        ->not->toContain($admin->id);
});

it('allows only admin to open user management', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $normal = User::factory()->create();

    $this->actingAs($admin)->get(route('users.index'))->assertOk();
    $this->actingAs($normal)->get(route('users.index'))->assertForbidden();
});
