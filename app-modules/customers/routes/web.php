<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Presentation\Http\Controllers\CustomerController;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customers.create')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customers.update')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update')->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');
});
