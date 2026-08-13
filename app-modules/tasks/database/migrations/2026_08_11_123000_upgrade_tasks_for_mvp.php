<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('reference', 32)->nullable()->after('id');
            $table->foreignId('created_by')->nullable()->after('project_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 32)->default('waiting_admin')->after('description')->index();
            $table->string('priority', 20)->default('normal')->after('status')->index();
            $table->date('due_date')->nullable()->after('priority')->index();
            $table->timestamp('completed_at')->nullable()->after('due_date')->index();
        });

        if (DB::table('tasks')->exists()) {
            $creatorId = DB::table('users')->where('role', 'admin')->value('id')
                ?? DB::table('users')->value('id');

            if (! $creatorId) {
                throw new RuntimeException('Cannot migrate existing tasks without an existing user.');
            }

            DB::table('tasks')->orderBy('id')->get()->each(function (object $task) use ($creatorId): void {
                do {
                    $reference = 'TSK-'.Str::upper(Str::random(8));
                } while (DB::table('tasks')->where('reference', $reference)->exists());

                DB::table('tasks')->where('id', $task->id)->update([
                    'reference' => $reference,
                    'created_by' => $creatorId,
                    'status' => $task->is_done ? 'completed' : 'waiting_admin',
                    'priority' => 'normal',
                    'completed_at' => $task->is_done ? ($task->updated_at ?? now()) : null,
                ]);
            });
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->string('reference', 32)->nullable(false)->change();
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->unique('reference');
            $table->dropColumn('is_done');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->boolean('is_done')->default(false)->index();
        });

        DB::table('tasks')->where('status', 'completed')->update(['is_done' => true]);

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropUnique(['reference']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'reference',
                'created_by',
                'assigned_to',
                'status',
                'priority',
                'due_date',
                'completed_at',
            ]);
        });
    }
};
