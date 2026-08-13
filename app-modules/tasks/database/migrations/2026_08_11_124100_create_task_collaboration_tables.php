<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('task_comments');
    }
};
