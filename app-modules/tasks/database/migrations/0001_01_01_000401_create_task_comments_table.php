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
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};
