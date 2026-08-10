<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        if (! Schema::hasColumn('projects', 'contact_id')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('projects', 'category')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->string('category', 20)->default('contact')->index();
            });
        }

        if (Schema::hasColumn('projects', 'customer_id')) {
            DB::table('projects')
                ->whereNotNull('customer_id')
                ->whereNull('contact_id')
                ->orderBy('id')
                ->get(['id', 'customer_id'])
                ->each(function (object $project): void {
                    $customer = Schema::hasTable('customers')
                        ? DB::table('customers')->where('id', $project->customer_id)->first(['person_id'])
                        : null;
                    $contactId = $customer?->person_id;

                    if (! $contactId || ! DB::table('contacts')->where('id', $contactId)->exists()) {
                        throw new RuntimeException("Cannot migrate project {$project->id}: customer could not be mapped to a contact.");
                    }

                    DB::table('projects')->where('id', $project->id)->update(['contact_id' => $contactId]);
                });
        }

        DB::table('projects')->where('category', 'customer')->update(['category' => 'contact']);

        if (DB::table('projects')->where('category', 'contact')->whereNull('contact_id')->exists()) {
            throw new RuntimeException('Cannot finalize contact projects: some projects are not mapped to contacts.');
        }

        if (Schema::hasColumn('projects', 'customer_id')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('customer_id');
            });
        }
    }

    public function down(): void
    {
        // Forward-only: projects now reference contacts directly.
    }
};
