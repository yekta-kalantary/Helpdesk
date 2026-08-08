<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;

beforeEach(function (): void {
    view()->share('errors', new ViewErrorBag);
});

it('renders semantic left-to-right inputs with left aligned text', function (string $name, string $type): void {
    $html = Blade::render('<x-ui.input name="'.$name.'" type="'.$type.'" />');

    expect($html)
        ->toContain('dir="ltr"')
        ->toContain('text-left');
})->with([
    'email' => ['email', 'email'],
    'mobile' => ['mobile', 'text'],
    'password' => ['password', 'password'],
    'password confirmation' => ['password_confirmation', 'password'],
    'telephone' => ['phone', 'tel'],
    'url' => ['website', 'url'],
    'number' => ['port', 'number'],
]);

it('does not force ordinary text inputs to left-to-right', function (): void {
    $html = Blade::render('<x-ui.input name="name" type="text" />');

    expect($html)
        ->not->toContain('dir="ltr"')
        ->not->toContain('text-left');
});

it('respects an explicit direction override', function (): void {
    $html = Blade::render('<x-ui.input name="email" type="email" dir="rtl" />');

    expect($html)
        ->toContain('dir="rtl"')
        ->not->toContain('text-left');
});

it('renders a distinct disabled state for shared form fields', function (): void {
    $input = Blade::render('<x-ui.input name="email" type="email" disabled />');
    $select = Blade::render('<x-ui.select name="role" disabled><option value="">Role</option></x-ui.select>');
    $textarea = Blade::render('<x-ui.textarea name="notes" disabled />');
    $checkbox = Blade::render('<x-ui.checkbox name="active" label="Active" disabled />');

    expect($input)
        ->toContain('disabled')
        ->toContain('disabled:bg-slate-100')
        ->toContain('disabled:cursor-not-allowed');

    expect($select)
        ->toContain('disabled')
        ->toContain('disabled:bg-slate-100')
        ->toContain('disabled:cursor-not-allowed');

    expect($textarea)
        ->toContain('disabled')
        ->toContain('disabled:bg-slate-100')
        ->toContain('disabled:cursor-not-allowed');

    expect($checkbox)
        ->toContain('disabled')
        ->toContain('aria-disabled="true"')
        ->toContain('cursor-not-allowed')
        ->toContain('bg-slate-100');
});
