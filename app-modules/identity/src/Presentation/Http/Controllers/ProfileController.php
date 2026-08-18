<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Application\UpdateUserContactInformation;
use Modules\Identity\Application\UpdateUserPersonalInformation;
use Modules\Identity\Presentation\Http\Requests\UpdateContactInformationRequest;
use Modules\Identity\Presentation\Http\Requests\UpdatePersonalInformationRequest;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Identity/Profile/Edit', [
            'profile' => [
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ],
            ],
        ]);
    }

    public function updatePersonalInformation(
        UpdatePersonalInformationRequest $request,
        UpdateUserPersonalInformation $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->validated());

        return to_route('profile.edit')
            ->with('status', __('identity::messages.general_saved'));
    }

    public function updateContactInformation(
        UpdateContactInformationRequest $request,
        UpdateUserContactInformation $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->validated());

        return to_route('profile.edit')
            ->with('status', __('identity::messages.contact_saved'));
    }
}
