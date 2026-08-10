<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Infrastructure\Models\User;

class Form extends Component
{
    #[Locked]
    public ?int $userId = null;

    public string $activeTab = 'general';

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $mobile = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = false;

    public function mount(?int $user = null): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        if (! $user) {
            return;
        }

        $item = User::query()
            ->where('is_admin', false)
            ->findOrFail($user);

        $this->userId = $item->id;
        $this->name = $item->name;
        $this->last_name = $item->last_name;
        $this->email = $item->email ?? '';
        $this->mobile = $item->mobile;
        $this->is_active = $item->is_active;
    }

    public function saveGeneral(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->userId) {
            $this->user()->update($data);
            session()->flash('success', __('identity::messages.general_saved'));

            return;
        }

        $user = User::query()->create([
            ...$data,
            'email' => null,
            'mobile' => null,
            'password' => null,
            'is_active' => false,
            'is_admin' => false,
        ]);

        $this->userId = $user->id;
        $this->activeTab = 'contact';

        session()->flash('success', __('identity::messages.general_saved'));
    }

    public function saveContact(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'mobile' => ['nullable', 'string', 'max:32'],
        ]);

        $this->user()->update([
            'email' => $data['email'] ?: null,
            'mobile' => $data['mobile'] ?: null,
        ]);

        session()->flash('success', __('identity::messages.contact_saved'));
    }

    public function saveAccount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ]);

        $user = $this->user();

        if ($data['is_active'] && blank($user->email)) {
            $this->addError('is_active', __('identity::messages.email_required_to_activate'));

            return;
        }

        if ($data['is_active'] && blank($user->getRawOriginal('password')) && $data['password'] === '') {
            $this->addError('password', __('identity::messages.password_required_to_activate'));

            return;
        }

        $attributes = [
            'is_active' => $data['is_active'],
        ];

        if ($data['password'] !== '') {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);

        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('success', __('identity::messages.account_saved'));
    }

    private function user(): User
    {
        abort_unless($this->userId, 404);

        return User::query()
            ->where('is_admin', false)
            ->findOrFail($this->userId);
    }

    public function render()
    {
        return view('identity::users.form')
            ->title($this->userId ? __('identity::messages.edit_user') : __('identity::messages.new_user'));
    }
}
