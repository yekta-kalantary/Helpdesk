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
        $columns = array_values(array_filter(
            ['last_name', 'mobile'],
            static fn (string $column): bool => Schema::hasColumn('users', $column),
        ));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
