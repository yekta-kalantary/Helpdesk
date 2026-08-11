<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->restrictOnDelete();
            $table->string('role', 20)->default('customer')->after('client_id')->index();
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        DB::table('users')
            ->where('is_admin', true)
            ->update(['role' => 'admin', 'client_id' => null]);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->index();
        });

        DB::table('users')->where('role', 'admin')->update(['is_admin' => true]);

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'role', 'last_login_at']);
        });
    }
};
