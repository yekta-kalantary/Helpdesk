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
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('project_status_id')->index();
            $table->unsignedBigInteger('work_group_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
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
