<?php

use Modules\Identity\Infrastructure\Models\User;

it('shows normal users to admin', function (): void {
    $admin = User::query()->where('is_admin', true)->firstOrFail();
    $normal = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($normal->email)
        ->assertDontSee($admin->email);
});

it('blocks normal users from user management', function (): void {
    $normal = User::factory()->create();

    $this->actingAs($normal)
        ->get(route('users.index'))
        ->assertForbidden();
});
