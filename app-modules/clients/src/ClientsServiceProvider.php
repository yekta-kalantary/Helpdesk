<?php

namespace Modules\Clients;

use Illuminate\Support\ServiceProvider;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Clients\Infrastructure\Queries\EloquentClientStatusQuery;

class ClientsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientStatusQuery::class, EloquentClientStatusQuery::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
