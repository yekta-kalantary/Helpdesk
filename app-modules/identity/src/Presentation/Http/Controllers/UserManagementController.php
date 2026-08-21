<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Clients\Application\DTOs\ClientSummary;
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
            ->latest()
            ->paginate(15);

        $clientsById = collect($activeClientDirectory->executeForIds(
            $users->getCollection()
                ->pluck('client_id')
                ->filter()
                ->map(fn (mixed $clientId): int => (int) $clientId)
                ->unique()
                ->values()
                ->all(),
        ))->keyBy('id');

        $users->through(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
            'client_id' => $user->client_id,
            'client' => $clientsById->get($user->client_id) instanceof ClientSummary
                ? [
                    'id' => $clientsById->get($user->client_id)->id,
                    'name' => $clientsById->get($user->client_id)->name,
                ]
                : null,
        ]);

        $roles = collect(UserRole::cases())->mapWithKeys(
            fn (UserRole $role): array => [$role->value => __('identity::messages.roles.'.$role->value)],
        )->all();

        return Inertia::render('Identity/Users/Index', [
            'users' => $users,
            'clients' => collect($activeClientDirectory->execute())
                ->map(fn (ClientSummary $client): array => ['id' => $client->id, 'name' => $client->name])
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
