<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('upgrades valid legacy users, projects, and memberships without deleting history', function (): void {
    $database = useLegacyUpgradeDatabase();

    try {
        createLegacyUpgradeSchema();

        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Legacy',
                'last_name' => 'Admin',
                'email' => 'admin@example.test',
                'is_admin' => true,
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Ada',
                'last_name' => 'Customer',
                'email' => 'ada@example.test',
                'is_admin' => false,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Grace',
                'last_name' => 'Customer',
                'email' => 'grace@example.test',
                'is_admin' => false,
                'is_active' => true,
            ],
        ]);

        DB::table('projects')->insert([
            ['id' => 10, 'title' => 'Ada project'],
            ['id' => 11, 'title' => 'Grace project'],
        ]);
        DB::table('project_user')->insert([
            ['project_id' => 10, 'user_id' => 1],
            ['project_id' => 10, 'user_id' => 2],
            ['project_id' => 10, 'user_id' => 3],
            ['project_id' => 11, 'user_id' => 3],
        ]);

        runMigration('app-modules/clients/database/migrations/2026_08_11_120000_create_clients_table.php');
        runMigration('app-modules/identity/database/migrations/2026_08_11_121000_upgrade_users_for_mvp.php');
        runMigration('app-modules/projects/database/migrations/2026_08_11_122000_upgrade_projects_for_mvp.php');

        $users = DB::table('users')->orderBy('id')->get();
        $adaClientId = $users->firstWhere('id', 2)->client_id;
        $graceClientId = $users->firstWhere('id', 3)->client_id;

        expect($adaClientId)->toBeInt()
            ->and($graceClientId)->toBeInt()
            ->and($adaClientId)->not->toBe($graceClientId)
            ->and(DB::table('clients')->where('id', $adaClientId)->value('name'))->toBe('Ada Customer')
            ->and(DB::table('clients')->where('id', $graceClientId)->value('name'))->toBe('Grace Customer')
            ->and(DB::table('projects')->where('id', 10)->value('client_id'))->toBe($adaClientId)
            ->and(DB::table('projects')->where('id', 11)->value('client_id'))->toBe($graceClientId)
            ->and(DB::table('project_user')->count())->toBe(4)
            ->and(DB::table('project_user')->where('project_id', 10)->where('user_id', 1)->exists())->toBeTrue()
            ->and(DB::table('project_user')->where('project_id', 10)->where('user_id', 2)->whereNull('removed_at')->exists())->toBeTrue()
            ->and(DB::table('project_user')->where('project_id', 10)->where('user_id', 3)->whereNotNull('removed_at')->exists())->toBeTrue();
    } finally {
        restoreApplicationDatabase($database);
    }
});

it('stops before changing the user schema when a legacy non-admin email is invalid', function (): void {
    $database = useLegacyUpgradeDatabase();

    try {
        createLegacyUpgradeSchema();
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Missing',
            'last_name' => 'Email',
            'email' => null,
            'is_admin' => false,
            'is_active' => true,
        ]);
        runMigration('app-modules/clients/database/migrations/2026_08_11_120000_create_clients_table.php');

        expect(fn (): mixed => runMigration('app-modules/identity/database/migrations/2026_08_11_121000_upgrade_users_for_mvp.php'))
            ->toThrow(RuntimeException::class, 'Legacy non-admin user 2 has an invalid email; provide a valid email address before retrying the upgrade.');

        expect(Schema::hasColumn('users', 'client_id'))->toBeFalse()
            ->and(DB::table('users')->where('id', 2)->value('email'))->toBeNull()
            ->and(DB::table('clients')->count())->toBe(0);
    } finally {
        restoreApplicationDatabase($database);
    }
});

function useLegacyUpgradeDatabase(): string
{
    $defaultConnection = (string) config('database.default');
    config([
        'database.connections.legacy_upgrade' => config('database.connections.sqlite'),
        'database.connections.legacy_upgrade.database' => ':memory:',
        'database.default' => 'legacy_upgrade',
    ]);
    DB::purge('legacy_upgrade');
    DB::reconnect('legacy_upgrade');

    return $defaultConnection;
}

function restoreApplicationDatabase(string $database): void
{
    DB::disconnect('legacy_upgrade');
    config(['database.default' => $database]);
}

function createLegacyUpgradeSchema(): void
{
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('last_name');
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->boolean('is_active')->default(false);
        $table->boolean('is_admin')->default(false);
        $table->index('is_admin');
        $table->timestamps();
    });

    Schema::create('projects', function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->timestamps();
    });

    Schema::create('project_user', function (Blueprint $table): void {
        $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
        $table->primary(['project_id', 'user_id']);
    });
}

function runMigration(string $path): void
{
    $migration = require base_path($path);
    $migration->up();
}
