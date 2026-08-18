<?php

it('switches to a supported locale and preserves a safe path', function (): void {
    $this->get(route('locale.switch', ['locale' => 'en', 'redirect' => '/']))
        ->assertRedirect('/');

    expect(session('locale'))->toBe('en');

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('direction', 'ltr'));
});

it('rejects unsupported locales', function (): void {
    $this->get(route('locale.switch', ['locale' => 'de']))
        ->assertNotFound();
});

it('rejects unsafe locale redirect targets', function (): void {
    $this->get(route('locale.switch', [
        'locale' => 'en',
        'redirect' => 'https://attacker.example.test',
    ]))->assertRedirect(route('home'));
});
