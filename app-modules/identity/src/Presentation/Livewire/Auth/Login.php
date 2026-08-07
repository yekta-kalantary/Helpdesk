<?php

namespace Modules\Identity\Presentation\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with('person')
            ->whereHas('person', fn ($query) => $query->where('email', $credentials['email']))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->addError('email', __('identity::messages.invalid_credentials'));
            $this->reset('password');

            return null;
        }

        if (! $user->is_active) {
            $this->addError('email', __('identity::messages.inactive_account'));
            $this->reset('password');

            return null;
        }

        Auth::login($user, $this->remember);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('identity::auth.login')->title(__('identity::messages.login_title'));
    }
}
