<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
};
