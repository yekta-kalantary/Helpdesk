<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'client', except: '')]
    public string $client = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->normalizeFilters();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedClient(): void
    {
        $this->normalizeFilters();
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->normalizeFilters();
        $this->resetPage();
    }

    private function normalizeFilters(): void
    {
        $this->client = ctype_digit($this->client) && Client::query()->whereKey((int) $this->client)->exists()
            ? (string) (int) $this->client
            : '';
        $this->status = in_array($this->status, ['active', 'inactive'], true)
            ? $this->status
            : '';
    }

    public function render()
    {
        $term = trim($this->q);

        $users = User::query()
            ->customers()
            ->with('client:id,name')
            ->when($term !== '', fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")))
            ->when($this->client !== '', fn ($query) => $query->where('client_id', (int) $this->client))
            ->when($this->status !== '', fn ($query) => $query->where('is_active', $this->status === 'active'))
            ->orderBy('name')
            ->orderBy('last_name')
            ->paginate(15);

        return view('identity::users.index', [
            'users' => $users,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
        ])
            ->title(__('identity::messages.users'));
    }
}
