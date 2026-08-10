<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks') || ! Schema::hasColumn('tasks', 'is_customer_visible')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['is_customer_visible']);
            $table->dropColumn('is_customer_visible');
        });
    }

    public function down(): void
    {
        // Forward-only: customer portal visibility no longer exists.
    }
};
