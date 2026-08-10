<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Infrastructure\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $this->prepareUsers();
        $this->simplifyProjects();
        $this->simplifyTasks();
        $this->removeLegacyTables();
    }

    public function down(): void
    {
        // Forward-only simplification.
    }

    private function prepareUsers(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable();
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable();
            }
            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 32)->nullable();
            }
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->index();
            }
        });

        if (Schema::hasTable('contacts') && Schema::hasColumn('users', 'contact_id')) {
            DB::table('users')
                ->join('contacts', 'contacts.id', '=', 'users.contact_id')
                ->select([
                    'users.id',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.email',
                    'contacts.mobile',
                ])
                ->orderBy('users.id')
                ->get()
                ->each(function (object $row): void {
                    DB::table('users')->where('id', $row->id)->update([
                        'name' => $row->first_name,
                        'last_name' => $row->last_name,
                        'email' => $row->email,
                        'mobile' => $row->mobile,
                    ]);
                });
        }

        if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
            $adminIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'admin')
                ->where('model_has_roles.model_type', User::class)
                ->pluck('model_has_roles.model_id');

            if ($adminIds->isNotEmpty()) {
                DB::table('users')->whereIn('id', $adminIds)->update(['is_admin' => true]);
            }
        }

        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'name', 'last_name', 'email'])
            ->each(function (object $user): void {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => $user->name ?: 'User',
                    'last_name' => $user->last_name ?: (string) $user->id,
                    'email' => $user->email ?: "user-{$user->id}@local.invalid",
                ]);
            });

        if (Schema::hasColumn('users', 'contact_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('contact_id');
            });
        }
    }

    private function simplifyProjects(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        if (Schema::hasColumn('projects', 'contact_id')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('contact_id');
            });
        }

        $columns = array_values(array_filter([
            'category',
            'type',
            'status',
            'starts_at',
            'ends_at',
            'deleted_at',
        ], fn (string $column): bool => Schema::hasColumn('projects', $column)));

        if ($columns !== []) {
            Schema::table('projects', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    private function simplifyTasks(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        if (! Schema::hasColumn('tasks', 'is_done')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->boolean('is_done')->default(false)->index();
            });
        }

        if (Schema::hasColumn('tasks', 'status')) {
            DB::table('tasks')->where('status', 'done')->update(['is_done' => true]);
        }

        Schema::dropIfExists('task_comments');

        foreach (['assigned_to', 'created_by'] as $foreignColumn) {
            if (Schema::hasColumn('tasks', $foreignColumn)) {
                Schema::table('tasks', function (Blueprint $table) use ($foreignColumn): void {
                    $table->dropConstrainedForeignId($foreignColumn);
                });
            }
        }

        $columns = array_values(array_filter([
            'priority',
            'status',
            'due_at',
            'estimated_minutes',
            'spent_minutes',
            'deleted_at',
        ], fn (string $column): bool => Schema::hasColumn('tasks', $column)));

        if ($columns !== []) {
            Schema::table('tasks', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    private function removeLegacyTables(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('contacts');
    }
};
