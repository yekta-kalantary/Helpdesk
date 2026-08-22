<?php

namespace App\Providers;

use App\Policies\AttachmentPolicy;
use App\Policies\ClientPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
    }
}
