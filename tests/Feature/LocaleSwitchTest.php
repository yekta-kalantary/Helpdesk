<?php

it('switches to a supported locale and preserves a safe path', function (): void {
    $this->get(route('locale.switch', [
        'locale' => 'en',
        'redirect' => '/projects?tab=active&sort=name',
    ]))->assertRedirect('/projects?tab=active&sort=name');

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
    foreach ([
        'https://attacker.example.test',
        '//attacker.example.test/path',
        '/\\attacker.example.test/path',
        '/%5Cattacker.example.test/path',
        '/%00attacker.example.test/path',
        "\/\u{0000}attacker.example.test",
    ] as $redirect) {
        $this->get(route('locale.switch', [
            'locale' => 'en',
            'redirect' => $redirect,
        ]))->assertRedirect(route('home'));
    }
});
