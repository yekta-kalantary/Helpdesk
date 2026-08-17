<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'identity');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
