<?php

namespace Modules\Identity\Presentation\Livewire\Roles;

use DomainException;
use Livewire\Component;
use Modules\Identity\Domain\Contracts\AccessControl;

class Index extends Component
{
    protected AccessControl $access;

    public function boot(AccessControl $access): void
    {
        $this->access = $access;
    }

    public function delete(int $role): void
    {
        abort_unless(auth()->user()?->can('roles.delete'), 403);

        $item = collect($this->access->roles())->firstWhere('id', $role);
        abort_if(! $item, 404);

        if ($item['system']) {
            $this->addError('role', __('identity::messages.system_role_immutable'));

            return;
        }

        try {
            $this->access->deleteRole($role);
            session()->flash('success', __('app.deleted_successfully'));
        } catch (DomainException $exception) {
            $this->addError('role', __('identity::messages.'.$exception->getMessage()));
        }
    }

    public function render()
    {
        return view('identity::roles.index', [
            'roles' => $this->access->roles(),
            'permissions' => $this->access->permissions(),
        ])->title(__('app.roles_permissions'));
    }
}
