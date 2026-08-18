<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Application\AuthenticateUser;
use Modules\Identity\Application\RequestPasswordReset;
use Modules\Identity\Presentation\Http\Requests\LoginRequest;
use Modules\Identity\Presentation\Http\Requests\PasswordRecoveryRequest;

class AuthenticationController
{
    public function create(): Response
    {
        return Inertia::render('Identity/Auth/Login', [
            'auth' => [
                'user' => null,
                'canResetPassword' => true,
                'canRememberSession' => true,
                'locale' => app()->getLocale(),
                'direction' => app()->getLocale() === 'fa' ? 'rtl' : 'ltr',
            ],
        ]);
    }

    public function store(LoginRequest $request, AuthenticateUser $authenticateUser): RedirectResponse
    {
        $authenticateUser->execute(
            email: (string) $request->string('email'),
            password: (string) $request->string('password'),
            remember: $request->boolean('remember'),
            throttleKey: strtolower($request->string('email').'|'.$request->ip()),
        );

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function forgotPassword(): Response
    {
        return Inertia::render('Identity/Auth/ForgotPassword', [
            'auth' => [
                'user' => null,
                'canLogin' => true,
                'locale' => app()->getLocale(),
                'direction' => app()->getLocale() === 'fa' ? 'rtl' : 'ltr',
            ],
            'status' => session('status'),
        ]);
    }

    public function sendPasswordResetLink(
        PasswordRecoveryRequest $request,
        RequestPasswordReset $requestPasswordReset,
    ): RedirectResponse {
        $requestPasswordReset->execute((string) $request->string('email'));

        return redirect()
            ->route('password.request')
            ->with('status', __('identity::messages.password_recovery.confirmation_description'));
    }
}
