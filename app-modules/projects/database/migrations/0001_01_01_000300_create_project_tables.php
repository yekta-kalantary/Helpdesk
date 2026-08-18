<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table): void {
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at');
            $table->timestamp('removed_at')->nullable()->index();
            $table->timestamps();
            $table->primary(['project_id', 'user_id']);
        });

        Schema::create('project_task_statuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->string('title', 120);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_done')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'is_active', 'position']);
            $table->index(['project_id', 'is_active', 'is_done']);
        });

        Schema::create('work_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('work_groups')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'parent_id', 'position']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_groups');
        Schema::dropIfExists('project_task_statuses');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
