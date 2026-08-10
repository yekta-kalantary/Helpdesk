<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/contacts', 'contacts::index')
        ->middleware('permission:contacts.view')
        ->name('contacts.index');

    Route::livewire('/contacts/create', 'contacts::form')
        ->middleware('permission:contacts.create')
        ->name('contacts.create');

    Route::livewire('/contacts/{contact}', 'contacts::form')
        ->middleware('permission:contacts.update')
        ->name('contacts.edit');
});
