<?php

namespace Modules\Clients;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ClientsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'clients');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'clients',
            classNamespace: 'Modules\\Clients\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
