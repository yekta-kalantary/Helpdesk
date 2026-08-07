<?php

namespace Modules\Projects;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Infrastructure\EloquentProjectRepository;

class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'projects');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'projects');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'projects',
            classNamespace: 'Modules\\Projects\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );
    }
}
