<?php

namespace Modules\Tickets\Presentation\Livewire;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Tickets\Application\Queries\TicketAccessScope;
use Modules\Tickets\Domain\Contracts\TicketRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'project', except: null)]
    public ?int $projectId = null;

    protected TicketRepository $tickets;

    protected TicketAccessScope $scopeBuilder;

    public function boot(TicketRepository $tickets, TicketAccessScope $scopeBuilder): void
    {
        $this->tickets = $tickets;
        $this->scopeBuilder = $scopeBuilder;
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tickets::index', [
            'tickets' => $this->tickets->search($scope, $this->projectId, trim($this->q) ?: null),
        ])->title(__('tickets::messages.tickets'));
    }
}
