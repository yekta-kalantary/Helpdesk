<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;
use Throwable;

class Form extends Component
{
    public ?int $client_id = null;

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $mobile = null;

    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $requestedClient = request()->integer('client');
        $this->client_id = $requestedClient ?: null;
    }

    public function save()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->email = Str::lower(trim($this->email));

        $data = $this->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['nullable', 'string', 'max:32'],
            'is_active' => ['boolean'],
        ]);

        $user = User::query()->create([
            'client_id' => (int) $data['client_id'],
            'role' => UserRole::Customer,
            'name' => trim($data['name']),
            'last_name' => trim($data['last_name']),
            'email' => $data['email'],
            'mobile' => filled($data['mobile']) ? trim($data['mobile']) : null,
            'password' => null,
            'is_active' => $data['is_active'],
        ]);

        if ($user->is_active) {
            try {
                Password::sendResetLink(['email' => $user->email]);
            } catch (Throwable $e) {
                Log::warning('Customer setup email delivery failed.', [
                    'user_id' => $user->id,
                    'exception' => $e::class,
                ]);
            }
        }

        session()->flash('success', __('app.created_successfully'));

        return $this->redirectRoute('users.show', ['user' => $user->id], navigate: true);
    }

    public function render()
    {
        $clients = Client::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('identity::users.form', compact('clients'))
            ->title(__('identity::messages.new_user'));
    }
}
