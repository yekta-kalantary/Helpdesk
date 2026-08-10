<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('people') || ! Schema::hasTable('contacts')) {
            return;
        }

        DB::table('people')
            ->orderBy('id')
            ->get()
            ->each(function (object $person): void {
                $email = trim((string) $person->email);
                $emailOwner = DB::table('contacts')->where('email', $email)->first(['id']);

                if ($emailOwner && (int) $emailOwner->id !== (int) $person->id) {
                    throw new RuntimeException("Cannot migrate person {$person->id}: contact email {$email} is already owned by contact {$emailOwner->id}.");
                }

                DB::table('contacts')->updateOrInsert(
                    ['id' => (int) $person->id],
                    [
                        'first_name' => trim((string) $person->first_name),
                        'last_name' => trim((string) $person->last_name),
                        'gender' => null,
                        'email' => $email,
                        'mobile' => trim((string) $person->mobile),
                        'province' => null,
                        'city' => null,
                        'address' => null,
                        'postal_code' => null,
                        'created_at' => $person->created_at ?? now(),
                        'updated_at' => $person->updated_at ?? now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        // Forward-only: contacts are the new source of identity data.
    }
};
