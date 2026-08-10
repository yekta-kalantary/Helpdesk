<?php

use Illuminate\Support\Facades\Schema;

it('has the core domain tables', function (): void {
    expect(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasTable('projects'))->toBeTrue()
        ->and(Schema::hasTable('project_user'))->toBeTrue()
        ->and(Schema::hasTable('tasks'))->toBeTrue();
});

it('keeps the core schemas minimal', function (): void {
    expect(Schema::hasColumns('users', ['name', 'last_name', 'email', 'mobile', 'password', 'is_active', 'is_admin']))->toBeTrue()
        ->and(Schema::hasColumns('projects', ['title', 'description']))->toBeTrue()
        ->and(Schema::hasColumns('project_user', ['project_id', 'user_id']))->toBeTrue()
        ->and(Schema::hasColumns('tasks', ['project_id', 'title', 'description', 'is_done']))->toBeTrue();
});
