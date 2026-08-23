<?php

namespace App\Providers;

use App\Integration\Notifications\EloquentNotifiableDirectory;
use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Application\Contracts\NotifiableDirectory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotifiableDirectory::class, EloquentNotifiableDirectory::class);
    }

    public function boot(): void {}
}
