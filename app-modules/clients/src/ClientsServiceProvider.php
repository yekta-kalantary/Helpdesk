<?php

namespace Modules\Clients;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Clients\Application\Contracts\ClientStatusQuery;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Clients\Infrastructure\Queries\EloquentClientStatusQuery;
use Modules\Clients\Presentation\Policies\ClientPolicy;

class ClientsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientStatusQuery::class, EloquentClientStatusQuery::class);
    }

    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
