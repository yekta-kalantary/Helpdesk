<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('category', 20)->default('customer')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('projects')->whereNull('customer_id')->exists()) {
            throw new RuntimeException('Cannot rollback project category migration while internal projects exist.');
        }

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
