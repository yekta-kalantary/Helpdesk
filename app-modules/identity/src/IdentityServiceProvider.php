<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Identity\Domain\Contracts\AccessControl;
use Modules\Identity\Domain\Contracts\UserRepository;
use Modules\Identity\Infrastructure\EloquentUserRepository;
use Modules\Identity\Infrastructure\SpatieAccessControl;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccessControl::class, SpatieAccessControl::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'identity');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'identity');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'identity',
            classNamespace: 'Modules\\Identity\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );
    }
}
