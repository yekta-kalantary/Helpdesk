<?php

namespace Modules\Clients\Presentation\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Projects\Domain\Enums\ProjectStatus;

class Show extends Component
{
    #[Locked]
    public int $clientId;

    public function mount(int $client): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->clientId = Client::query()->findOrFail($client)->id;
    }

    public function render()
    {
        $client = Client::query()
            ->with(['users' => fn ($query) => $query->orderBy('name')->orderBy('last_name')])
            ->withCount([
                'users',
                'projects',
                'projects as active_projects_count' => fn ($query) => $query->where('status', ProjectStatus::Active->value),
            ])
            ->findOrFail($this->clientId);

        $projects = $client->projects()->latest('id')->limit(10)->get();

        return view('clients::show', compact('client', 'projects'))->title($client->name);
    }
}
