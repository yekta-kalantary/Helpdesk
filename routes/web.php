<?php

use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('/notifications', 'notifications.index')->name('notifications.index');
});

Route::get('/', static function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');
