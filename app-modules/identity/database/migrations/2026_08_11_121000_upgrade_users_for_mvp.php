<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('is_admin', false)
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function (object $user): void {
                if (! is_string($user->email) || filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
                    throw new RuntimeException(
                        "Legacy non-admin user {$user->id} has an invalid email; provide a valid email address before retrying the upgrade."
                    );
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->restrictOnDelete();
            $table->string('role', 20)->default('customer')->after('client_id')->index();
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        DB::table('users')
            ->where('is_admin', true)
            ->update(['role' => 'admin', 'client_id' => null]);

        DB::table('users')
            ->where('role', 'customer')
            ->orderBy('id')
            ->get(['id', 'name', 'last_name'])
            ->each(function (object $user): void {
                $clientId = DB::table('clients')->insertGetId([
                    'name' => trim("{$user->name} {$user->last_name}"),
                    'description' => 'Created automatically while upgrading a legacy customer user.',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->where('id', $user->id)->update(['client_id' => $clientId]);
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_admin']);
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
