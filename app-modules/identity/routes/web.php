<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::livewire('/login', 'identity::auth.login')->name('login');
        Route::livewire('/forgot-password', 'identity::auth.forgot-password')->name('password.request');
        Route::livewire('/reset-password/{token}', 'identity::auth.reset-password')->name('password.reset');
    });

    Route::middleware(['auth', 'account.active'])->group(function (): void {
        Route::livewire('/profile', 'identity::profile')->name('profile');
        Route::livewire('/users', 'identity::users.index')->name('users.index');
        Route::livewire('/users/create', 'identity::users.form')->name('users.create');
        Route::livewire('/users/{user}', 'identity::users.show')->name('users.show');
        Route::livewire('/users/{user}/edit', 'identity::users.show')->name('users.edit');
    });
});
