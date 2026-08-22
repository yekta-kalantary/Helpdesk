<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;
use Modules\Identity\Application\Contracts\AccountDirectory;
use Modules\Identity\Infrastructure\Queries\EloquentAccountDirectory;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountDirectory::class, EloquentAccountDirectory::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'identity');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
