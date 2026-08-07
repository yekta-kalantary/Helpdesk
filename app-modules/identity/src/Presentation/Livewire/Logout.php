<?php

namespace Modules\Identity\Presentation\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('identity::logout');
    }
}
