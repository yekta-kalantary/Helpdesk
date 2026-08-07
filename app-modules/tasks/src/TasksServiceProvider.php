<?php

namespace Modules\Tasks;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Tasks\Domain\Contracts\TaskAttachmentReader;
use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Domain\Contracts\TaskNotifier;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Infrastructure\DatabaseTaskNotifier;
use Modules\Tasks\Infrastructure\EloquentTaskRepository;
use Modules\Tasks\Infrastructure\LocalTaskAttachmentReader;
use Modules\Tasks\Infrastructure\LocalTaskAttachmentStore;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
        $this->app->bind(TaskAttachmentStore::class, LocalTaskAttachmentStore::class);
        $this->app->bind(TaskAttachmentReader::class, LocalTaskAttachmentReader::class);
        $this->app->bind(TaskNotifier::class, DatabaseTaskNotifier::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tasks');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tasks');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'tasks',
            classNamespace: 'Modules\\Tasks\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views',
        );
    }
}
