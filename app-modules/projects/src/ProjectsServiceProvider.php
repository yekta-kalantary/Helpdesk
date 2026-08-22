<?php

namespace Modules\Projects;

use Illuminate\Support\ServiceProvider;
use Modules\Projects\Application\Contracts\ProjectAccessQuery;
use Modules\Projects\Application\Contracts\ProjectMembershipDirectory;
use Modules\Projects\Infrastructure\Queries\EloquentProjectAccessQuery;
use Modules\Projects\Infrastructure\Queries\EloquentProjectMembershipDirectory;

class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProjectMembershipDirectory::class, EloquentProjectMembershipDirectory::class);
        $this->app->bind(ProjectAccessQuery::class, EloquentProjectAccessQuery::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'projects');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
