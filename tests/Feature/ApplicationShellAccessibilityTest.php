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
    $dashboard = file_get_contents(resource_path('js/Pages/Dashboard.vue'));
    $shell = file_get_contents(resource_path('js/Layouts/AppShell.vue'));
    $mobileNavigation = file_get_contents(resource_path('js/components/app-shell/MobileNavigation.vue'));
    $sidebar = file_get_contents(resource_path('js/components/app-shell/Sidebar.vue'));
    $topBar = file_get_contents(resource_path('js/components/app-shell/TopBar.vue'));
    $userMenu = file_get_contents(resource_path('js/components/app-shell/UserMenu.vue'));

    // No browser harness is available in this suite, so verify the rendered accessibility contract statically.
    expect($app)->not->toContain('AppShell');
    expect($dashboard)
        ->toContain("import AppShell from '@/Layouts/AppShell.vue'")
        ->toContain('layout: AppShell');
    expect($shell)
        ->toContain('<main')
        ->toContain('<div')
        ->toContain(':dir="page.props.direction"')
        ->toContain('nextTick()')
        ->toContain('mobileNavigationTrigger.value?.focus()');
    expect($topBar)
        ->toContain('<header')
        ->toContain('focus-visible:ring-2')
        ->toContain('aria-controls="mobile-navigation"')
        ->toContain('aria-haspopup="dialog"')
        ->toContain(':aria-expanded="navigationOpen"');
    expect($sidebar)
        ->toContain('<aside')
        ->toContain('<nav')
        ->toContain('focus-visible:ring-2')
        ->toContain(':aria-current="isNavigationItemActive(item, currentUrl) ? \'page\' : undefined"');
    expect($mobileNavigation)
        ->toContain('role="presentation"')
        ->toContain('aria-modal="true"')
        ->toContain(':aria-label="navigationLabel"')
        ->toContain('focus-visible:ring-2')
        ->toContain("event.key === 'Escape'")
        ->toContain('closeButton.value?.focus()')
        ->toContain("'a[href], button:not([disabled]), [tabindex]:not([tabindex=\"-1\"])'")
        ->toContain('return backdrop.value ? [backdrop.value, ...drawerElements] : drawerElements')
        ->toContain('motion-reduce:transition-none')
        ->toContain('enter-active-class="transition-opacity duration-200 ease-out motion-reduce:transition-none"')
        ->toContain('leave-active-class="transition-transform duration-150 ease-in motion-reduce:transition-none"');
    expect($userMenu)
        ->toContain('@keydown.esc="closeMenu"')
        ->toContain('trigger.value?.focus()')
        ->toContain('document.addEventListener(\'pointerdown\', closeOnOutsideClick)')
        ->toContain('document.removeEventListener(\'pointerdown\', closeOnOutsideClick)')
        ->toContain('menu.value?.contains(event.target as Node)');

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
