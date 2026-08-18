<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_status_id')->constrained('project_task_statuses')->restrictOnDelete();
            $table->foreignId('work_group_id')->nullable()->constrained('work_groups')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
