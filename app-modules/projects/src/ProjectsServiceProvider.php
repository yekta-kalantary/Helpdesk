<?php

namespace Modules\Projects;

use Illuminate\Support\ServiceProvider;

class ProjectsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'projects');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
