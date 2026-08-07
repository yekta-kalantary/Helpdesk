<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;
use Modules\Identity\Domain\Contracts\AccessControl;
use Modules\Identity\Infrastructure\SpatieAccessControl;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccessControl::class, SpatieAccessControl::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'identity');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'identity');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
