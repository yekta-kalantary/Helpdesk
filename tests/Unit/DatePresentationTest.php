<?php

use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('renders dates and date-times with the shared display formats', function (): void {
    config(['app.timezone' => 'Asia/Tehran']);

    $view = view('components.ui.date', [
        'value' => Carbon::parse('2026-08-13 14:05:00', 'UTC'),
        'datetime' => false,
    ])->render();

    expect($view)->toContain('2026/08/13')
        ->not->toContain('14:05');

    $view = view('components.ui.date', [
        'value' => Carbon::parse('2026-08-13 14:05:00', 'UTC'),
        'datetime' => true,
    ])->render();

    expect($view)->toContain('2026/08/13 17:35');
});

it('renders an empty value without inventing a date', function (): void {
    expect(view('components.ui.date', ['value' => null])->render())->toBe('');
});
