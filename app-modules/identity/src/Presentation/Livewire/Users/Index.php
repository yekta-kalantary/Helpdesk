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

    public function delete(int $user): void
    {
        abort_unless(auth()->user()?->can('users.delete'), 403);
        abort_if(auth()->id() === $user, 422, __('identity::messages.cannot_delete_yourself'));

        $this->users->delete($user);
        session()->flash('success', __('app.deleted_successfully'));
    }

    public function render()
    {
        return view('identity::users.index', [
            'users' => $this->users->search(trim($this->q) ?: null),
        ])->title(__('identity::messages.users'));
    }
}
