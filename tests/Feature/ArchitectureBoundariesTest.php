<?php

use Illuminate\Support\Facades\Schema;

it('inventories cross-context infrastructure imports before the boundary migration', function (): void {
    $violations = [];

    foreach (moduleSourceFiles() as [$module, $relativePath, $source]) {
        if (preg_match(crossContextInfrastructureImportPattern($module), $source) === 1) {
            $violations[$relativePath] = $module;
        }
    }

    expect($violations)->not->toBeEmpty()
        ->and(array_values(array_unique($violations)))->toContain('clients', 'identity', 'projects', 'tasks');
});

it('has the MVP and project work management domain tables', function (): void {
    expect(Schema::hasTable('clients'))->toBeTrue()
        ->and(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasTable('projects'))->toBeTrue()
        ->and(Schema::hasTable('project_user'))->toBeTrue()
        ->and(Schema::hasTable('project_task_statuses'))->toBeTrue()
        ->and(Schema::hasTable('work_groups'))->toBeTrue()
        ->and(Schema::hasTable('tasks'))->toBeTrue()
        ->and(Schema::hasTable('task_checklist_items'))->toBeTrue()
        ->and(Schema::hasTable('task_comments'))->toBeTrue()
        ->and(Schema::hasTable('attachments'))->toBeTrue()
        ->and(Schema::hasTable('activities'))->toBeTrue()
        ->and(Schema::hasTable('notifications'))->toBeTrue();
});

it('keeps the core schemas aligned with project-owned workflow boundaries', function (): void {
    expect(Schema::hasColumns('clients', ['name', 'description', 'status']))->toBeTrue()
        ->and(Schema::hasColumns('users', ['client_id', 'role', 'name', 'last_name', 'email', 'mobile', 'password', 'is_active', 'last_login_at']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'is_admin'))->toBeFalse()
        ->and(Schema::hasColumns('projects', ['client_id', 'name', 'description', 'status', 'start_date', 'due_date']))->toBeTrue()
        ->and(Schema::hasColumns('project_user', ['project_id', 'user_id', 'joined_at', 'removed_at']))->toBeTrue()
        ->and(Schema::hasColumns('project_task_statuses', ['project_id', 'title', 'position', 'is_done', 'is_active', 'created_by', 'inactivated_at']))->toBeTrue()
        ->and(Schema::hasColumns('work_groups', ['project_id', 'parent_id', 'title', 'description', 'position', 'status', 'created_by', 'inactivated_at']))->toBeTrue()
        ->and(Schema::hasColumns('tasks', ['reference', 'project_id', 'project_status_id', 'work_group_id', 'created_by', 'assigned_to', 'title', 'description', 'priority', 'due_date', 'completed_at']))->toBeTrue()
        ->and(Schema::hasColumn('tasks', 'status'))->toBeFalse()
        ->and(Schema::hasColumn('tasks', 'is_done'))->toBeFalse()
        ->and(Schema::hasColumns('task_checklist_items', ['task_id', 'title', 'is_completed', 'position', 'created_by', 'removed_at']))->toBeTrue()
        ->and(Schema::hasColumn('task_checklist_items', 'parent_id'))->toBeFalse()
        ->and(Schema::hasColumn('task_checklist_items', 'assigned_to'))->toBeFalse()
        ->and(Schema::hasColumn('task_checklist_items', 'project_status_id'))->toBeFalse()
        ->and(Schema::hasColumns('task_comments', ['task_id', 'user_id', 'body', 'hidden_at', 'hidden_by']))->toBeTrue()
        ->and(Schema::hasColumns('attachments', ['task_id', 'comment_id', 'uploaded_by', 'original_name', 'storage_path', 'mime_type', 'size', 'hidden_at', 'hidden_by']))->toBeTrue()
        ->and(Schema::hasColumns('activities', ['actor_id', 'project_id', 'task_id', 'action', 'metadata']))->toBeTrue();
});

function moduleSourceFiles(): iterable
{
    $modulesPath = base_path('app-modules');
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulesPath));

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = str_replace($modulesPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
        [$module] = explode(DIRECTORY_SEPARATOR, $relativePath, 2);

        yield [$module, $relativePath, file_get_contents($file->getPathname())];
    }
}

function crossContextInfrastructureImportPattern(string $module): string
{
    return '/^use Modules\\\\(?!'.preg_quote(ucfirst($module), '/').'\\\\)\\w+\\\\Infrastructure\\\\/m';
}
