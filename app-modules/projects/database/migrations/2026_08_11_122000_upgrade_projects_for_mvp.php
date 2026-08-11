<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->restrictOnDelete();
            $table->string('status', 20)->default('active')->after('description')->index();
            $table->date('start_date')->nullable()->after('status');
            $table->date('due_date')->nullable()->after('start_date');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->renameColumn('title', 'name');
        });

        if (DB::table('projects')->exists()) {
            $clientId = DB::table('users')
                ->where('role', 'customer')
                ->whereNotNull('client_id')
                ->value('client_id');

            if (! $clientId) {
                $clientId = DB::table('clients')->insertGetId([
                    'name' => 'Migrated Client',
                    'description' => 'Created automatically while upgrading existing projects.',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('projects')->whereNull('client_id')->update(['client_id' => $clientId]);
        }

        Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
        });

        Schema::table('project_user', function (Blueprint $table): void {
            $table->timestamp('joined_at')->nullable()->after('user_id');
            $table->timestamp('removed_at')->nullable()->after('joined_at')->index();
        });

        DB::table('project_user')->whereNull('joined_at')->update([
            'joined_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
        ]);

        Schema::table('project_user', function (Blueprint $table): void {
            $table->timestamp('joined_at')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_user', function (Blueprint $table): void {
            $table->dropColumn(['joined_at', 'removed_at']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->renameColumn('name', 'title');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'status', 'start_date', 'due_date']);
        });
    }
};
