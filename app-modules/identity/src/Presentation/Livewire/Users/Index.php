<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Identity\Domain\Contracts\UserRepository;

class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $q = '';

    protected UserRepository $users;

    public function boot(UserRepository $users): void
    {
        $this->users = $users;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public function render()
    {
        return view('identity::users.index', [
            'users' => $this->users->search(trim($this->q) ?: null),
        ])->title(__('identity::messages.users'));
    }
}
