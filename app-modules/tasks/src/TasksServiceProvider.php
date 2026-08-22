<?php

namespace Modules\Tasks;

use Illuminate\Support\ServiceProvider;
use Modules\Tasks\Application\Contracts\ProjectTaskStateQuery;
use Modules\Tasks\Application\Contracts\ProjectTaskStateWriter;
use Modules\Tasks\Application\ProjectTaskState;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectTaskState::class);
        $this->app->bind(ProjectTaskStateQuery::class, ProjectTaskState::class);
        $this->app->bind(ProjectTaskStateWriter::class, ProjectTaskState::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
