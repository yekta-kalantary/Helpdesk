<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Clients\Application\Queries\ActiveClientDirectory;
use Modules\Identity\Application\CreateUser;
use Modules\Identity\Domain\Enums\UserRole;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Identity\Presentation\Http\Requests\CreateUserRequest;

class UserManagementController
{
    public function index(Request $request, ActiveClientDirectory $activeClientDirectory): Response
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $users = User::query()
            ->with('client:id,name')
            ->latest()
            ->paginate(15)
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->role->value,
                'is_active' => $user->is_active,
                'client' => $user->client ? [
                    'id' => $user->client->id,
                    'name' => $user->client->name,
                ] : null,
            ]);

        $roles = collect(UserRole::cases())->mapWithKeys(
            fn (UserRole $role): array => [$role->value => __('identity::messages.roles.'.$role->value)],
        )->all();

        return Inertia::render('Identity/Users/Index', [
            'users' => $users,
            'clients' => collect($activeClientDirectory->execute())
                ->map(fn ($client): array => ['id' => $client->id, 'name' => $client->name])
                ->values()
                ->all(),
            'roles' => array_keys($roles),
            'roleLabels' => $roles,
        ]);
    }

    public function store(CreateUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $createUser->execute($request->validated());

        return to_route('users.index')
            ->with('status', __('identity::messages.user_created'));
    }
}
