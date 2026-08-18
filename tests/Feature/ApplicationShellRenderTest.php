<?php

it('renders the application shell contract for responsive navigation', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('appName', config('app.name'))
            ->where('navigationLabel', __('app.navigation.label'))
            ->where('navigationCloseLabel', __('app.navigation.close'))
            ->where('navigation.0.key', 'overview')
            ->where('navigation.0.items.0.href', route('dashboard'))
            ->where('auth.user', null));
});

it('renders the Persian close label for the responsive navigation contract', function (): void {
    $this->app->setLocale('fa');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('navigationLabel', __('app.navigation.label'))
            ->where('navigationCloseLabel', __('app.navigation.close'))
            ->where('direction', 'rtl'));
});
