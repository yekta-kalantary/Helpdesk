<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::livewire('/login', 'identity::auth.login')->name('login');
    });

    Route::middleware('auth')->group(function (): void {
        Route::livewire('/users', 'identity::users.index')->name('users.index');
        Route::livewire('/users/create', 'identity::users.form')->name('users.create');
        Route::livewire('/users/{user}/edit', 'identity::users.form')->name('users.edit');
    });
});
