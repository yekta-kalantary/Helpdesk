<?php

namespace Modules\Identity\Presentation\Livewire;

use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

class Profile extends Component
{
    public string $name = '';

    public string $last_name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->name = $user->name;
        $this->last_name = $user->last_name;
    }

    public function save(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $attributes = [
            'name' => trim($data['name']),
            'last_name' => trim($data['last_name']),
        ];

        if ($data['password'] !== '') {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);
        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('success', __('app.updated_successfully'));
    }

    public function render()
    {
        return view('identity::profile')->title('پروفایل');
    }
}
