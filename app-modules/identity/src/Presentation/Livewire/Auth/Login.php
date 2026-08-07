<?php

namespace Modules\Identity\Presentation\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', __('identity::messages.invalid_credentials'));
            $this->reset('password');

            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $this->addError('email', __('identity::messages.inactive_account'));
            $this->reset('password');

            return null;
        }

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('identity::auth.login')->title(__('identity::messages.login_title'));
    }
}
