<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeOrphanedLegacyMedia();

        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('people');
    }

    public function down(): void
    {
        // Forward-only integration cleanup. Removed bounded contexts are not recreated.
    }

    private function removeOrphanedLegacyMedia(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'model_type')) {
            return;
        }

        DB::table('media')->whereIn('model_type', [
            'Modules\\Tickets\\Infrastructure\\Models\\Ticket',
            'Modules\\Tickets\\Infrastructure\\Models\\TicketMessage',
            'Modules\\Customers\\Infrastructure\\Models\\Customer',
            'App\\Models\\Person',
        ])->delete();
    }
};
