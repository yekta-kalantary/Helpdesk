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
        'database/migrations/0001_01_01_000001_create_cache_tables.php',
        'database/migrations/0001_01_01_000002_create_queue_tables.php',
        'app-modules/clients/database/migrations/0001_01_01_000100_create_clients_table.php',
        'app-modules/identity/database/migrations/0001_01_01_000200_create_identity_tables.php',
        'app-modules/projects/database/migrations/0001_01_01_000300_create_project_tables.php',
        'app-modules/tasks/database/migrations/0001_01_01_000400_create_task_tables.php',
        'database/migrations/0001_01_01_000500_create_activity_and_notification_tables.php',
    ];
}

function runMigration(string $path): void
{
    $migration = require base_path($path);
    $migration->up();
}
