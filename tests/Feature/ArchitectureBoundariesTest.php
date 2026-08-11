<?php

use Illuminate\Support\Facades\Schema;

it('has the MVP domain tables', function (): void {
    expect(Schema::hasTable('clients'))->toBeTrue()
        ->and(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasTable('projects'))->toBeTrue()
        ->and(Schema::hasTable('project_user'))->toBeTrue()
        ->and(Schema::hasTable('tasks'))->toBeTrue()
        ->and(Schema::hasTable('task_comments'))->toBeTrue()
        ->and(Schema::hasTable('attachments'))->toBeTrue()
        ->and(Schema::hasTable('activities'))->toBeTrue()
        ->and(Schema::hasTable('notifications'))->toBeTrue();
});

it('keeps the core schemas aligned with the MVP domain boundaries', function (): void {
    expect(Schema::hasColumns('clients', ['name', 'description', 'status']))->toBeTrue()
        ->and(Schema::hasColumns('users', ['client_id', 'role', 'name', 'last_name', 'email', 'mobile', 'password', 'is_active', 'last_login_at']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'is_admin'))->toBeFalse()
        ->and(Schema::hasColumns('projects', ['client_id', 'name', 'description', 'status', 'start_date', 'due_date']))->toBeTrue()
        ->and(Schema::hasColumns('project_user', ['project_id', 'user_id', 'joined_at', 'removed_at']))->toBeTrue()
        ->and(Schema::hasColumns('tasks', ['reference', 'project_id', 'created_by', 'assigned_to', 'title', 'description', 'status', 'priority', 'due_date', 'completed_at']))->toBeTrue()
        ->and(Schema::hasColumn('tasks', 'is_done'))->toBeFalse()
        ->and(Schema::hasColumns('task_comments', ['task_id', 'user_id', 'body', 'hidden_at', 'hidden_by']))->toBeTrue()
        ->and(Schema::hasColumns('attachments', ['task_id', 'comment_id', 'uploaded_by', 'original_name', 'storage_path', 'mime_type', 'size', 'hidden_at', 'hidden_by']))->toBeTrue()
        ->and(Schema::hasColumns('activities', ['actor_id', 'project_id', 'task_id', 'action', 'metadata']))->toBeTrue();
});
