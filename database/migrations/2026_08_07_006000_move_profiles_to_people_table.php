<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyUsers = Schema::hasTable('users')
            && (! Schema::hasColumn('users', 'person_id') || Schema::hasColumn('users', 'email'));
        $legacyCustomers = Schema::hasTable('customers')
            && (! Schema::hasColumn('customers', 'person_id') || Schema::hasColumn('customers', 'email'));

        if (! $legacyUsers && ! $legacyCustomers) {
            return;
        }

        if (! Schema::hasTable('people')) {
            Schema::create('people', function (Blueprint $table): void {
                $table->id();
                $table->string('type', 20)->index();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('mobile', 32);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'person_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('person_id')->nullable()->unique()->constrained('people')->restrictOnDelete();
            });
        }

        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'person_id')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->foreignId('person_id')->nullable()->unique()->constrained('people')->restrictOnDelete();
            });
        }

        $this->migrateCustomers();
        $this->migrateEmployees();
        $this->makePersonReferencesRequired();
        $this->dropLegacyCustomerProfile();
        $this->dropLegacyUserProfile();
    }

    public function down(): void
    {
        // Forward-only data migration: restoring duplicated profile columns would recreate two sources of truth.
    }

    private function migrateCustomers(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'person_id')) {
            return;
        }

        $hasUserId = Schema::hasColumn('customers', 'user_id');
        $hasName = Schema::hasColumn('customers', 'name');
        $hasEmail = Schema::hasColumn('customers', 'email');
        $hasPhone = Schema::hasColumn('customers', 'phone');

        if (! $hasName || ! $hasEmail) {
            return;
        }

        DB::table('customers')
            ->whereNull('person_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $customer) use ($hasUserId, $hasPhone): void {
                $user = $hasUserId && $customer->user_id
                    ? DB::table('users')->where('id', $customer->user_id)->first()
                    : null;

                $email = trim((string) $customer->email);
                $existing = DB::table('people')->where('email', $email)->first();

                if ($existing) {
                    throw new \RuntimeException("Cannot migrate customer {$customer->id}: email {$email} is already assigned to another person.");
                }

                $personId = DB::table('people')->insertGetId([
                    'type' => 'customer',
                    'first_name' => trim((string) $customer->name),
                    'last_name' => trim((string) ($user->last_name ?? '')),
                    'email' => $email,
                    'mobile' => trim((string) ($user->mobile ?? ($hasPhone ? $customer->phone : '') ?? '')),
                    'created_at' => $customer->created_at ?? now(),
                    'updated_at' => $customer->updated_at ?? now(),
                ]);

                DB::table('customers')->where('id', $customer->id)->update(['person_id' => $personId]);

                if ($user) {
                    DB::table('users')->where('id', $user->id)->update(['person_id' => $personId]);
                }
            });
    }

    private function migrateEmployees(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'person_id') || ! Schema::hasColumn('users', 'email')) {
            return;
        }

        DB::table('users')
            ->whereNull('person_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                $email = trim((string) $user->email);
                $existing = DB::table('people')->where('email', $email)->first();

                if ($existing) {
                    throw new \RuntimeException("Cannot migrate user {$user->id}: email {$email} already belongs to a customer person. Resolve the duplicate identity before migrating.");
                }

                $personId = DB::table('people')->insertGetId([
                    'type' => 'employee',
                    'first_name' => trim((string) $user->name),
                    'last_name' => trim((string) $user->last_name),
                    'email' => $email,
                    'mobile' => trim((string) $user->mobile),
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]);

                DB::table('users')->where('id', $user->id)->update(['person_id' => $personId]);
            });
    }

    private function makePersonReferencesRequired(): void
    {
        foreach (['users', 'customers'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'person_id')) {
                continue;
            }

            if (DB::table($tableName)->whereNull('person_id')->exists()) {
                throw new \RuntimeException("Cannot finalize {$tableName}.person_id: some rows could not be mapped to people.");
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('person_id')->nullable(false)->change();
            });
        }
    }

    private function dropLegacyCustomerProfile(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        $columns = collect(['name', 'company', 'email', 'phone'])
            ->filter(fn (string $column): bool => Schema::hasColumn('customers', $column))
            ->values()
            ->all();

        if ($columns !== []) {
            Schema::table('customers', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    private function dropLegacyUserProfile(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $columns = collect(['name', 'last_name', 'email', 'mobile'])
            ->filter(fn (string $column): bool => Schema::hasColumn('users', $column))
            ->values()
            ->all();

        if ($columns !== []) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
