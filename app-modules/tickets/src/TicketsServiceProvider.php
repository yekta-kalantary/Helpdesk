<?php

namespace Modules\Tickets;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Domain\Contracts\TicketNotifier;
use Modules\Tickets\Domain\Contracts\TicketRepository;
use Modules\Tickets\Infrastructure\DatabaseTicketNotifier;
use Modules\Tickets\Infrastructure\EloquentTicketRepository;
use Modules\Tickets\Infrastructure\LocalTicketAttachmentStore;

class TicketsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
        $this->app->bind(TicketAttachmentStore::class, LocalTicketAttachmentStore::class);
        $this->app->bind(TicketNotifier::class, DatabaseTicketNotifier::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tickets');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tickets');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Livewire::addNamespace(
            namespace: 'tickets',
            classNamespace: 'Modules\\Tickets\\Presentation\\Livewire',
            classPath: __DIR__.'/Presentation/Livewire',
            classViewPath: __DIR__.'/../resources/views/livewire',
        );
    }
}
