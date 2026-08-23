<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('work_groups')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamp('inactivated_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'parent_id', 'position']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_groups');
    }
};
