<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $user = $request->user();
        $capabilities = $user?->isAdmin() === true
            ? ['users.view', 'clients.view', 'projects.view', 'tasks.view']
            : [];

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'locale' => $locale,
            'direction' => $locale === 'fa' ? 'rtl' : 'ltr',
            'navigationLabel' => __('app.navigation.label'),
            'navigationCloseLabel' => __('app.navigation.close'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->getKey(),
                    'name' => $user->full_name,
                    'email' => $user->email,
                ] : null,
                'capabilities' => $capabilities,
            ],
            'navigation' => [
                [
                    'key' => 'overview',
                    'label' => __('app.navigation.overview'),
                    'items' => [
                        ['key' => 'dashboard', 'label' => __('navigation.dashboard'), 'href' => route('dashboard'), 'icon' => 'dashboard'],
                    ],
                ],
                [
                    'key' => 'workspace',
                    'label' => __('app.navigation.workspace'),
                    'items' => [
                        ['key' => 'clients', 'label' => __('navigation.clients'), 'href' => null, 'icon' => 'building', 'pending' => true, 'capability' => 'clients.view'],
                        ['key' => 'projects', 'label' => __('navigation.projects'), 'href' => null, 'icon' => 'projects', 'pending' => true, 'capability' => 'projects.view'],
                        ['key' => 'tasks', 'label' => __('navigation.tasks'), 'href' => null, 'icon' => 'tasks', 'pending' => true, 'capability' => 'tasks.view'],
                    ],
                ],
                [
                    'key' => 'administration',
                    'label' => __('app.navigation.administration'),
                    'items' => [
                        ['key' => 'users', 'label' => __('navigation.users'), 'href' => null, 'icon' => 'users', 'pending' => true, 'capability' => 'users.view'],
                    ],
                ],
            ],
            'translations' => [
                'app' => [
                    'userMenu' => [
                        'open' => __('app.user_menu.open'),
                        'close' => __('app.user_menu.close'),
                        'logout' => __('app.user_menu.logout'),
                    ],
                ],
                'identity' => [
                    'login' => __('identity::messages.login'),
                    'passwordRecovery' => __('identity::messages.password_recovery'),
                    'passwordReset' => __('identity::messages.password_reset'),
                ],
            ],
        ]);
    }
}
