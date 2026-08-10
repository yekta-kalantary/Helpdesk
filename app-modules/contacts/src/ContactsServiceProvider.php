<?php

namespace Modules\Contacts;

use Illuminate\Support\ServiceProvider;

class ContactsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Contacts migrations are kept only so existing databases can be upgraded
        // before the final simplification migration removes the legacy tables.
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
