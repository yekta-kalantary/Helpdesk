<?php

namespace Modules\Identity\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Identity\Application\AccountAuthenticationEligibility;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function __construct(private AccountAuthenticationEligibility $eligibility) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $this->eligibility->canAuthenticateAccount($user->id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('identity::messages.inactive_account'),
            ]);
        }

        return $next($request);
    }
}
