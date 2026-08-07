<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/customers', 'customers::index')
        ->middleware('permission:customers.view')
        ->name('customers.index');

    Route::livewire('/customers/create', 'customers::form')
        ->middleware('permission:customers.create')
        ->name('customers.create');

    Route::livewire('/customers/{customer}/edit', 'customers::form')
        ->middleware('permission:customers.update')
        ->name('customers.edit');
});
