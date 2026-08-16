<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('project_status_id')
                ->nullable()
                ->after('project_id')
                ->constrained('project_task_statuses')
                ->restrictOnDelete();
            $table->foreignId('work_group_id')
                ->nullable()
                ->after('project_status_id')
                ->constrained('work_groups')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['work_group_id']);
            $table->dropForeign(['project_status_id']);
            $table->dropColumn(['work_group_id', 'project_status_id']);
        });
    }
};
