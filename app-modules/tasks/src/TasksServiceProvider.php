<?php

namespace Modules\Tasks;

use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
