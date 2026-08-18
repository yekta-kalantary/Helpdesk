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

        Schema::create('task_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('body')->nullable();
            $table->timestamp('hidden_at')->nullable()->index();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('task_comments')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('original_name');
            $table->string('storage_path')->unique();
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size');
            $table->timestamp('hidden_at')->nullable()->index();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('task_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();
            $table->index(['task_id', 'removed_at', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklist_items');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('tasks');
    }
};
