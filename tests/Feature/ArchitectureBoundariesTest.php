<?php

use Illuminate\Support\Facades\Schema;

it('keeps only the simple core tables', function (): void {
    expect(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasTable('projects'))->toBeTrue()
        ->and(Schema::hasTable('project_user'))->toBeTrue()
        ->and(Schema::hasTable('tasks'))->toBeTrue()
        ->and(Schema::hasTable('contacts'))->toBeFalse()
        ->and(Schema::hasTable('roles'))->toBeFalse()
        ->and(Schema::hasTable('permissions'))->toBeFalse()
        ->and(Schema::hasTable('media'))->toBeFalse()
        ->and(Schema::hasTable('task_comments'))->toBeFalse();
});

it('keeps user project and task schemas minimal', function (): void {
    expect(Schema::hasColumns('users', ['name', 'last_name', 'email', 'mobile', 'password', 'is_active', 'is_admin']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'contact_id'))->toBeFalse()
        ->and(Schema::hasColumns('projects', ['title', 'description']))->toBeTrue()
        ->and(Schema::hasColumn('projects', 'contact_id'))->toBeFalse()
        ->and(Schema::hasColumn('projects', 'category'))->toBeFalse()
        ->and(Schema::hasColumns('tasks', ['project_id', 'title', 'description', 'is_done']))->toBeTrue()
        ->and(Schema::hasColumn('tasks', 'assigned_to'))->toBeFalse()
        ->and(Schema::hasColumn('tasks', 'priority'))->toBeFalse();
});
