<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Identity\Domain\Contracts\AccessControl;

class RoleController extends Controller
{
    public function __construct(private readonly AccessControl $access)
    {
    }

    public function index(): View
    {
        return view('identity::roles.index', [
            'roles' => $this->access->roles(),
            'permissions' => $this->access->permissions(),
        ]);
    }

    public function create(): View
    {
        return view('identity::roles.form', [
            'role' => null,
            'permissions' => $this->access->permissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'alpha_dash:ascii', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        try {
            $this->access->createRole($data['name'], $data['permissions'] ?? []);
        } catch (DomainException $exception) {
            return back()->withErrors(['name' => __('identity::messages.'.$exception->getMessage())])->withInput();
        }

        return redirect()->route('roles.index')->with('success', __('app.created_successfully'));
    }

    public function edit(int $role): View
    {
        $item = collect($this->access->roles())->firstWhere('id', $role);
        abort_if(! $item || $item['system'], 404);

        return view('identity::roles.form', [
            'role' => $item,
            'permissions' => $this->access->permissions(),
        ]);
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'alpha_dash:ascii', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        try {
            $this->access->updateRole($role, $data['name'], $data['permissions'] ?? []);
        } catch (DomainException $exception) {
            return back()->withErrors(['name' => __('identity::messages.'.$exception->getMessage())])->withInput();
        }

        return redirect()->route('roles.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $role): RedirectResponse
    {
        $item = collect($this->access->roles())->firstWhere('id', $role);
        abort_if(! $item, 404);

        if ($item['system']) {
            return back()->withErrors([
                'role' => __('identity::messages.system_role_immutable'),
            ]);
        }

        try {
            $this->access->deleteRole($role);
        } catch (DomainException $exception) {
            return back()->withErrors(['role' => __('identity::messages.'.$exception->getMessage())]);
        }

        return redirect()->route('roles.index')->with('success', __('app.deleted_successfully'));
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[a-z0-9_.-]+$/', 'max:125', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ]);

        $this->access->createPermission($data['name']);

        return redirect()->route('roles.index')->with('success', __('app.created_successfully'));
    }

    public function destroyPermission(int $permission): RedirectResponse
    {
        $this->access->deletePermission($permission);

        return redirect()->route('roles.index')->with('success', __('app.deleted_successfully'));
    }
}
