<?php

namespace Modules\Identity\Presentation\Livewire\Users;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Domain\Contracts\AccessControl;
use Modules\Identity\Domain\Contracts\UserRepository;

class Form extends Component
{
    #[Locked]
    public ?int $userId = null;

    #[Locked]
    public ?int $contactId = null;

    public string $name = '';
    public string $last_name = '';
    public string $email = '';
    public string $mobile = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public string $role = '';

    protected UserRepository $users;
    protected AccessControl $access;

    public function boot(UserRepository $users, AccessControl $access): void
    {
        $this->users = $users;
        $this->access = $access;
    }

    public function mount(?int $user = null): void
    {
        if (! $user) {
            return;
        }

        $item = $this->users->find($user);
        $this->userId = $user;
        $this->contactId = $item['contact_id'];
        $this->name = $item['name'];
        $this->last_name = $item['last_name'];
        $this->email = $item['email'];
        $this->mobile = $item['mobile'];
        $this->is_active = $item['is_active'];
        $this->role = $item['role'] ?? '';
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->userId ? 'users.update' : 'users.create'), 403);

        $data = $this->validate();

        if ($this->userId) {
            $this->users->update(
                $this->userId,
                $data['name'],
                $data['last_name'],
                $data['email'],
                $data['mobile'],
                $data['password'] ?: null,
                $data['is_active'],
                $data['role'],
            );
        } else {
            $this->users->create(
                $data['name'],
                $data['last_name'],
                $data['email'],
                $data['mobile'],
                $data['password'],
                $data['is_active'],
                $data['role'],
            );
        }

        session()->flash('success', $this->userId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('users.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($this->contactId)],
            'mobile' => ['required', 'string', 'max:32'],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'role' => ['required', 'string', Rule::notIn(['admin']), Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }

    private function teamRoles(): array
    {
        return array_values(array_filter(
            $this->access->roles(),
            static fn (array $role) => ! $role['system'],
        ));
    }

    public function render()
    {
        return view('identity::users.form', [
            'roles' => $this->teamRoles(),
        ])->title($this->userId ? __('identity::messages.edit_user') : __('identity::messages.new_user'));
    }
}
