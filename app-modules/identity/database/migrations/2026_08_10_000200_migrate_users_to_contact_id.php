<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'contact_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('contact_id')->nullable()->unique()->constrained('contacts')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'person_id')) {
            DB::table('users')
                ->whereNull('contact_id')
                ->orderBy('id')
                ->get(['id', 'person_id'])
                ->each(function (object $user): void {
                    if (! $user->person_id || ! DB::table('contacts')->where('id', $user->person_id)->exists()) {
                        throw new RuntimeException("Cannot migrate user {$user->id}: legacy person could not be mapped to a contact.");
                    }

                    DB::table('users')->where('id', $user->id)->update(['contact_id' => $user->person_id]);
                });
        }

        if (DB::table('users')->whereNull('contact_id')->exists()) {
            throw new RuntimeException('Cannot finalize users.contact_id: some users are not mapped to contacts.');
        }

        if (Schema::hasColumn('users', 'person_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('person_id');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_id')->nullable(false)->change();
        });

        $this->migrateAuthorizationModelTypes();
    }

    public function down(): void
    {
        // Forward-only: Identity now references Contacts and authorization morphs use the module-owned User class.
    }

    private function migrateAuthorizationModelTypes(): void
    {
        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'model_type')) {
                continue;
            }

            DB::table($table)
                ->where('model_type', 'App\\Models\\User')
                ->update(['model_type' => 'Modules\\Identity\\Infrastructure\\Models\\User']);
        }
    }
};
