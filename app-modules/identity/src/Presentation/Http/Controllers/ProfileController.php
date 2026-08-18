<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Application\UpdateUserContactInformation;
use Modules\Identity\Application\UpdateUserPassword;
use Modules\Identity\Application\UpdateUserPersonalInformation;
use Modules\Identity\Presentation\Http\Requests\UpdateContactInformationRequest;
use Modules\Identity\Presentation\Http\Requests\UpdateEmailRequest;
use Modules\Identity\Presentation\Http\Requests\UpdateMobileRequest;
use Modules\Identity\Presentation\Http\Requests\UpdatePersonalInformationRequest;
use Modules\Identity\Presentation\Http\Requests\UpdateUserPasswordRequest;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = [
            'user' => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ],
        ];

        if ($request->session()->has('profile_status')) {
            $profile['status'] = $request->session()->get('profile_status');
        }

        return Inertia::render('Identity/Profile/Edit', [
            'profile' => $profile,
        ]);
    }

    public function updatePersonalInformation(
        UpdatePersonalInformationRequest $request,
        UpdateUserPersonalInformation $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->validated());

        return to_route('profile.edit')
            ->with('profile_status', [
                'personal' => __('identity::messages.profile.personal.saved'),
            ]);
    }

    public function updateContactInformation(
        UpdateContactInformationRequest $request,
        UpdateUserContactInformation $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->validated());

        return to_route('profile.edit')
            ->with('profile_status', [
                'contact' => __('identity::messages.profile.contact.saved'),
            ]);
    }

    public function updateEmail(
        UpdateEmailRequest $request,
        UpdateUserContactInformation $action,
    ): RedirectResponse {
        $action->updateEmail($request->user(), $request->string('email')->toString());

        return to_route('profile.edit')
            ->with('profile_status', [
                'email' => __('identity::messages.profile.contact.email_saved'),
            ]);
    }

    public function updateMobile(
        UpdateMobileRequest $request,
        UpdateUserContactInformation $action,
    ): RedirectResponse {
        $action->updateMobile($request->user(), $request->input('mobile'));

        return to_route('profile.edit')
            ->with('profile_status', [
                'mobile' => __('identity::messages.profile.contact.mobile_saved'),
            ]);
    }

    public function updatePassword(
        UpdateUserPasswordRequest $request,
        UpdateUserPassword $action,
    ): RedirectResponse {
        $action->execute($request->user(), $request->string('password')->toString());

        return to_route('profile.edit')
            ->with('profile_status', [
                'password' => __('identity::messages.profile.password.saved'),
            ]);
    }
}
