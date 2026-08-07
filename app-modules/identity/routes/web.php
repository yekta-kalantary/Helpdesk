<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::livewire('/login', 'identity::auth.login')->name('login');
    });

    Route::middleware('auth')->group(function (): void {
        Route::livewire('/dashboard', 'identity::dashboard')->name('dashboard');

        Route::livewire('/notifications', 'identity::notifications.index')
            ->middleware('permission:notifications.view')
            ->name('notifications.index');

        Route::livewire('/users', 'identity::users.index')
            ->middleware('permission:users.view')
            ->name('users.index');
        Route::livewire('/users/create', 'identity::users.form')
            ->middleware('permission:users.create')
            ->name('users.create');
        Route::livewire('/users/{user}/edit', 'identity::users.form')
            ->middleware('permission:users.update')
            ->name('users.edit');

        Route::livewire('/roles', 'identity::roles.index')
            ->middleware('permission:roles.view')
            ->name('roles.index');
        Route::livewire('/roles/create', 'identity::roles.form')
            ->middleware('permission:roles.create')
            ->name('roles.create');
        Route::livewire('/roles/{role}/edit', 'identity::roles.form')
            ->middleware('permission:roles.update')
            ->name('roles.edit');
    });
});
