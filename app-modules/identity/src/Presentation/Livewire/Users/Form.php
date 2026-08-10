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

    public string $name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $mobile = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = true;

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
        $this->email = $item->email;
        $this->mobile = $item->mobile;
        $this->is_active = $item->is_active;
    }

    public function save()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $data = $this->validate();
        $attributes = [
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?: null,
            'is_active' => $data['is_active'],
        ];

        if ($this->userId) {
            $user = User::query()
                ->where('is_admin', false)
                ->findOrFail($this->userId);

            if ($data['password'] !== '') {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);
        } else {
            User::query()->create($attributes + [
                'password' => $data['password'],
                'is_admin' => false,
            ]);
        }

        session()->flash('success', $this->userId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('users.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'mobile' => ['nullable', 'string', 'max:32'],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];
    }

    public function render()
    {
        return view('identity::users.form')
            ->title($this->userId ? __('identity::messages.edit_user') : __('identity::messages.new_user'));
    }
}
