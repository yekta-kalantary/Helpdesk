<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_integration_events', function (Blueprint $table): void {
            $table->uuid('event_id');
            $table->string('consumer');
            $table->timestamp('processed_at');
            $table->unique(['event_id', 'consumer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_integration_events');
    }
};
