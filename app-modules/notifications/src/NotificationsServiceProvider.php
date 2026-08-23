<?php

namespace Modules\Notifications;

use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Application\Contracts\ResourceChangedNotificationFactory;
use Modules\Notifications\Infrastructure\Notifications\DefaultResourceChangedNotificationFactory;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResourceChangedNotificationFactory::class, DefaultResourceChangedNotificationFactory::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
