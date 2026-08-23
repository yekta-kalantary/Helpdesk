<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_statuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->string('title', 120);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_done')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'is_active', 'position']);
            $table->index(['project_id', 'is_active', 'is_done']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_statuses');
    }
};
