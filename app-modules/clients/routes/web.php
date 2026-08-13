<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'account.active'])->group(function (): void {
    Route::livewire('/clients', 'clients::index')->name('clients.index');
    Route::livewire('/clients/create', 'clients::form')->name('clients.create');
    Route::livewire('/clients/{client}', 'clients::show')->name('clients.show');
    Route::livewire('/clients/{client}/edit', 'clients::form')->name('clients.edit');
});
