<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/locale/{locale}', function (Request $request, string $locale): RedirectResponse {
    abort_unless(in_array($locale, ['en', 'fa'], true), 404);

    app()->setLocale($locale);
    $request->session()->put('locale', $locale);

    $redirect = (string) $request->query('redirect', route('home'));
    $parsedRedirect = parse_url($redirect);
    $isSafeRedirect = str_starts_with($redirect, '/')
        && ! str_starts_with($redirect, '//')
        && ! isset($parsedRedirect['scheme'], $parsedRedirect['host']);

    return redirect()->to($isSafeRedirect ? $redirect : route('home'));
})->whereIn('locale', ['en', 'fa'])->name('locale.switch');
