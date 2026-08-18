<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('creates the final schema from the fresh baseline migrations', function (): void {
    $database = useMigrationTestDatabase();

    try {
        foreach (baselineMigrationPaths() as $path) {
            runMigration($path);
        }

        expect(Schema::hasTable('cache'))->toBeTrue()
            ->and(Schema::hasTable('cache_locks'))->toBeTrue()
            ->and(Schema::hasTable('jobs'))->toBeTrue()
            ->and(Schema::hasTable('job_batches'))->toBeTrue()
            ->and(Schema::hasTable('failed_jobs'))->toBeTrue()
            ->and(Schema::hasTable('clients'))->toBeTrue()
            ->and(Schema::hasTable('users'))->toBeTrue()
            ->and(Schema::hasTable('password_reset_tokens'))->toBeTrue()
            ->and(Schema::hasTable('sessions'))->toBeTrue()
            ->and(Schema::hasTable('projects'))->toBeTrue()
            ->and(Schema::hasTable('project_user'))->toBeTrue()
            ->and(Schema::hasTable('project_task_statuses'))->toBeTrue()
            ->and(Schema::hasTable('work_groups'))->toBeTrue()
            ->and(Schema::hasTable('tasks'))->toBeTrue()
            ->and(Schema::hasTable('task_comments'))->toBeTrue()
            ->and(Schema::hasTable('attachments'))->toBeTrue()
            ->and(Schema::hasTable('task_checklist_items'))->toBeTrue()
            ->and(Schema::hasTable('activities'))->toBeTrue()
            ->and(Schema::hasTable('notifications'))->toBeTrue()
            ->and(Schema::hasColumn('users', 'role'))->toBeTrue()
            ->and(Schema::hasColumn('users', 'last_login_at'))->toBeTrue()
            ->and(Schema::hasColumn('users', 'is_admin'))->toBeFalse()
            ->and(Schema::hasColumn('projects', 'name'))->toBeTrue()
            ->and(Schema::hasColumn('projects', 'title'))->toBeFalse()
            ->and(Schema::hasColumn('tasks', 'reference'))->toBeTrue()
            ->and(Schema::hasColumn('tasks', 'project_status_id'))->toBeTrue()
            ->and(Schema::hasColumn('tasks', 'status'))->toBeFalse()
            ->and(Schema::hasColumn('activities', 'metadata'))->toBeTrue();
    } finally {
        restoreMigrationTestDatabase($database);
    }
});

function useMigrationTestDatabase(): string
{
    $defaultConnection = (string) config('database.default');
    config([
        'database.connections.migration_test' => config('database.connections.sqlite'),
        'database.connections.migration_test.database' => ':memory:',
        'database.default' => 'migration_test',
    ]);
    DB::purge('migration_test');
    DB::reconnect('migration_test');

    return $defaultConnection;
}

function restoreMigrationTestDatabase(string $database): void
{
    DB::disconnect('migration_test');
    config(['database.default' => $database]);
}

function baselineMigrationPaths(): array
{
    return [
        'database/migrations/0001_01_01_000001_create_cache_table.php',
        'database/migrations/0001_01_01_000002_create_cache_locks_table.php',
        'database/migrations/0001_01_01_000003_create_jobs_table.php',
        'database/migrations/0001_01_01_000004_create_job_batches_table.php',
        'database/migrations/0001_01_01_000005_create_failed_jobs_table.php',
        'app-modules/clients/database/migrations/0001_01_01_000100_create_clients_table.php',
        'app-modules/identity/database/migrations/0001_01_01_000200_create_users_table.php',
        'app-modules/identity/database/migrations/0001_01_01_000201_create_password_reset_tokens_table.php',
        'app-modules/identity/database/migrations/0001_01_01_000202_create_sessions_table.php',
        'app-modules/projects/database/migrations/0001_01_01_000300_create_projects_table.php',
        'app-modules/projects/database/migrations/0001_01_01_000301_create_project_user_table.php',
        'app-modules/projects/database/migrations/0001_01_01_000302_create_project_task_statuses_table.php',
        'app-modules/projects/database/migrations/0001_01_01_000303_create_work_groups_table.php',
        'app-modules/tasks/database/migrations/0001_01_01_000400_create_tasks_table.php',
        'app-modules/tasks/database/migrations/0001_01_01_000401_create_task_comments_table.php',
        'app-modules/tasks/database/migrations/0001_01_01_000402_create_attachments_table.php',
        'app-modules/tasks/database/migrations/0001_01_01_000403_create_task_checklist_items_table.php',
        'database/migrations/0001_01_01_000500_create_activities_table.php',
        'database/migrations/0001_01_01_000501_create_notifications_table.php',
    ];
}

function runMigration(string $path): void
{
    $migration = require base_path($path);
    $migration->up();
}
