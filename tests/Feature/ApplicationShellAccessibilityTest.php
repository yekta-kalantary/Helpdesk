<?php

use Modules\Identity\Infrastructure\Models\User;

it('protects the dashboard and renders its presentation contract through the shell', function (): void {
    $this->get(route('dashboard'))->assertRedirect(route('login'));

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('title', __('app.dashboard'))
            ->where('summary', __('app.dashboard_summary'))
            ->where('auth.user.id', $user->id));
});

it('keeps identity pages outside the authenticated shell and preserves accessibility contracts', function (): void {
    $app = file_get_contents(resource_path('js/app.ts'));
    $shell = file_get_contents(resource_path('js/Layouts/AppShell.vue'));
    $mobileNavigation = file_get_contents(resource_path('js/components/app-shell/MobileNavigation.vue'));
    $sidebar = file_get_contents(resource_path('js/components/app-shell/Sidebar.vue'));
    $topBar = file_get_contents(resource_path('js/components/app-shell/TopBar.vue'));

    expect($app)->not->toContain('AppShell');
    expect($shell)->toContain('<main');
    expect($shell)->toContain(':dir="page.props.direction"');
    expect($mobileNavigation)
        ->toContain('aria-modal="true"')
        ->toContain("event.key === 'Escape'")
        ->toContain('closeButton.value?.focus()');
    expect($sidebar)->toContain(':aria-current="isNavigationItemActive(item, currentUrl) ? \'page\' : undefined"');
    expect($topBar)->toContain(':aria-expanded="navigationOpen"');

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
            ->where('auth.direction', 'rtl'));
});

it('shares rtl presentation data for the authenticated dashboard', function (): void {
    $this->app->setLocale('fa');
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('direction', 'rtl')
            ->where('title', __('app.dashboard'))
            ->where('summary', __('app.dashboard_summary')));
});
