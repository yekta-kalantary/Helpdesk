<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'model_type')) {
            return;
        }

        Media::query()
            ->whereIn('model_type', [
                'Modules\\Tickets\\Infrastructure\\Models\\Ticket',
                'Modules\\Tickets\\Infrastructure\\Models\\TicketMessage',
                'Modules\\Customers\\Infrastructure\\Models\\Customer',
                'App\\Models\\Person',
            ])
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    public function down(): void
    {
        // Removed module media is intentionally not restored.
    }
};
