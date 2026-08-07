<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Identity\Domain\Contracts\AccessControl;
use Modules\Identity\Domain\Contracts\UserRepository;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly AccessControl $access,
    ) {}

    public function index(Request $request): View
    {
        return view('identity::users.index', [
            'users' => $this->users->search($request->string('q')->trim()->value() ?: null),
        ]);
    }

    public function create(): View
    {
        return view('identity::users.form', [
            'user' => null,
            'roles' => $this->teamRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $this->users->create(
            $data['name'],
            $data['email'],
            $data['password'],
            $request->boolean('is_active'),
            $data['roles'] ?? [],
        );

        return redirect()->route('users.index')->with('success', __('app.created_successfully'));
    }

    public function edit(int $user): View
    {
        return view('identity::users.form', [
            'user' => $this->users->find($user),
            'roles' => $this->teamRoles(),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $this->users->update(
            $user,
            $data['name'],
            $data['email'],
            $data['password'] ?? null,
            $request->boolean('is_active'),
            $data['roles'] ?? [],
        );

        return redirect()->route('users.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $user): RedirectResponse
    {
        abort_if(auth()->id() === $user, 422, __('identity::messages.cannot_delete_yourself'));
        $this->users->delete($user);

        return redirect()->route('users.index')->with('success', __('app.deleted_successfully'));
    }

    /** @return array<int, array{id:int,name:string,permissions:array<int,string>,system:bool}> */
    private function teamRoles(): array
    {
        return array_values(array_filter(
            $this->access->roles(),
            static fn (array $role) => ! in_array($role['name'], ['admin', 'customer'], true),
        ));
    }
}
