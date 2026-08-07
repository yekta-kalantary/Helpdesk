<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('last_name')->default('');
            });
        }

        if (! Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('mobile', 32)->default('');
            });
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: these columns are part of the canonical users schema.
    }
};
