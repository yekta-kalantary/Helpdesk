<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

class Form extends Component
{
    public string $name = '';

    public string $last_name = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public function save()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->create([
            ...$data,
            'email' => null,
            'mobile' => null,
            'password' => null,
            'is_active' => false,
            'is_admin' => false,
        ]);

        session()->flash('success', __('app.created_successfully'));

        return $this->redirectRoute('users.show', ['user' => $user->id], navigate: true);
    }

    public function render()
    {
        return view('identity::users.form')
            ->title(__('identity::messages.new_user'));
    }
}
