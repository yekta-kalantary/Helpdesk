<?php

namespace Modules\Media;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Domain\Contracts\MediaManager;
use Modules\Media\Infrastructure\SpatieMediaManager;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MediaManager::class, SpatieMediaManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
