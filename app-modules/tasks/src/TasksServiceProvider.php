<?php

namespace Modules\Tasks;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Projects\Application\Events\ProjectMembershipRemovedV1;
use Modules\Projects\Application\Events\ProjectTaskStatusChangedV1;
use Modules\Tasks\Application\Consumers\ProjectMembershipRemovedConsumer;
use Modules\Tasks\Application\Consumers\ProjectTaskStatusChangedConsumer;
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
        Event::listen(ProjectMembershipRemovedV1::class, [ProjectMembershipRemovedConsumer::class, 'handle']);
        Event::listen(ProjectTaskStatusChangedV1::class, [ProjectTaskStatusChangedConsumer::class, 'handle']);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
