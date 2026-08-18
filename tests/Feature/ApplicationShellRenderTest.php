<?php

it('renders the application shell contract for responsive navigation', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('appName', config('app.name'))
            ->where('navigationLabel', __('app.navigation.label'))
            ->where('navigation.0.key', 'overview')
            ->where('navigation.0.items.0.href', route('home'))
            ->where('auth.user', null));
});
