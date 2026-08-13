<?php

namespace Modules\Clients\Presentation\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Clients\Infrastructure\Models\Client;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->q);

        $clients = Client::query()
            ->when($term !== '', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->withCount(['users', 'projects'])
            ->orderBy('name')
            ->paginate(15);

        return view('clients::index', compact('clients'))->title('مشتریان');
    }
}
