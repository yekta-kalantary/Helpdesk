<?php

namespace Modules\Tasks;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class TasksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tasks');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'tasks',
            classNamespace: 'Modules\\Tasks\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
