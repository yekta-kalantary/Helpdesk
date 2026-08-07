<?php

namespace Modules\Identity\Presentation\Livewire\Roles;

use DomainException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Identity\Domain\Access\PermissionCatalog;
use Modules\Identity\Domain\Contracts\AccessControl;

class Form extends Component
{
    #[Locked]
    public ?int $roleId = null;

    public string $name = '';

    public array $permissions = [];

    protected AccessControl $access;

    public function boot(AccessControl $access): void
    {
        $this->access = $access;
    }

    public function mount(?int $role = null): void
    {
        if (! $role) {
            return;
        }

        $item = collect($this->access->roles())->firstWhere('id', $role);
        abort_if(! $item || $item['system'], 404);

        $this->roleId = $role;
        $this->name = $item['name'];
        $this->permissions = $item['permissions'];
    }

    public function save()
    {
        abort_unless(auth()->user()?->can($this->roleId ? 'roles.update' : 'roles.create'), 403);

        $data = $this->validate();

        try {
            if ($this->roleId) {
                $this->access->updateRole($this->roleId, $data['name'], $data['permissions'] ?? []);
            } else {
                $this->access->createRole($data['name'], $data['permissions'] ?? []);
            }
        } catch (DomainException $exception) {
            $this->addError('name', __('identity::messages.'.$exception->getMessage()));

            return null;
        }

        session()->flash('success', $this->roleId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('roles.index', navigate: true);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'alpha_dash:ascii', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($this->roleId)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::all())],
        ];
    }

    public function render()
    {
        return view('identity::roles.form', [
            'permissionCatalog' => $this->access->permissions(),
        ])->title($this->roleId ? __('identity::messages.edit_role') : __('identity::messages.new_role'));
    }
}
