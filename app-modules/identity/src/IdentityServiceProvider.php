<?php

namespace Modules\Identity;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class IdentityServiceProvider extends ServiceProvider
{
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
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
