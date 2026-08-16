<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('tasks')->whereNull('project_status_id')->exists()) {
            throw new RuntimeException('Cannot finalize task workflow while tasks have no Project Status.');
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_status_id')->nullable(false)->change();
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('status', 32)->default('waiting_admin')->after('description')->index();
        });

        DB::table('tasks')
            ->join('project_task_statuses', 'project_task_statuses.id', '=', 'tasks.project_status_id')
            ->select(['tasks.id', 'project_task_statuses.title', 'project_task_statuses.is_done'])
            ->orderBy('tasks.id')
            ->get()
            ->each(function (object $task): void {
                $legacyStatus = $task->is_done
                    ? 'completed'
                    : ($task->title === 'در حال انجام' ? 'in_progress' : 'waiting_admin');

                DB::table('tasks')->where('id', $task->id)->update(['status' => $legacyStatus]);
            });
    }
};
