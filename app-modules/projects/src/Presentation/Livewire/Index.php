<?php

namespace Modules\Projects\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'client', except: '')]
    public string $client = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedClient(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $term = trim($this->q);

        $projects = Project::query()
            ->visibleTo($user)
            ->with('client:id,name')
            ->withCount([
                'tasks',
                'activeMembers as members_count',
            ])
            ->when($term !== '', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($user->isAdmin() && $this->client !== '', fn ($query) => $query->where('client_id', (int) $this->client))
            ->orderByDesc('updated_at')
            ->paginate(15);

        $clients = $user->isAdmin()
            ? Client::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('projects::index', [
            'projects' => $projects,
            'clients' => $clients,
            'isAdmin' => $user->isAdmin(),
        ])->title(__('projects::messages.projects'));
    }
}
