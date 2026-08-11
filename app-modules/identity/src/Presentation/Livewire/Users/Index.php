<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function updatedQ(): void
    {
        $this->resetPage();
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
            ->orderBy('name')
            ->orderBy('last_name')
            ->paginate(15);

        return view('identity::users.index', compact('users'))
            ->title(__('identity::messages.users'));
    }
}
