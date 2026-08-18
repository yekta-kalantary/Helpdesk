<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'account.active'])
    ->name('dashboard');

Route::get('/locale/{locale}', function (Request $request, string $locale): RedirectResponse {
    abort_unless(in_array($locale, ['en', 'fa'], true), 404);

    app()->setLocale($locale);
    $request->session()->put('locale', $locale);

    $redirect = (string) $request->query('redirect', route('home'));
    $parsedRedirect = parse_url($redirect);
    $path = is_array($parsedRedirect) ? (string) ($parsedRedirect['path'] ?? '') : '';
    $decodedPath = rawurldecode($path);
    $hasUnsafeCharacters = str_contains($redirect, '\\')
        || preg_match('/[\x00-\x1F\x7F]/', $redirect) === 1
        || preg_match('/[\x00-\x1F\x7F]/', $decodedPath) === 1;
    $isSafeRedirect = ! $hasUnsafeCharacters
        && str_starts_with($path, '/')
        && ! str_starts_with($decodedPath, '//')
        && ! str_contains($decodedPath, '\\')
        && ! isset($parsedRedirect['scheme'])
        && ! isset($parsedRedirect['host']);

    return redirect()->to($isSafeRedirect ? $redirect : route('home'));
})->whereIn('locale', ['en', 'fa'])->name('locale.switch');
