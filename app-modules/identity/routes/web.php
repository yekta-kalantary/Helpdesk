<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Presentation\Http\Controllers\AuthenticationController;
use Modules\Identity\Presentation\Http\Controllers\ProfileController;

Route::middleware(['web', 'guest'])->group(function (): void {
    Route::get('/login', [AuthenticationController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticationController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [AuthenticationController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthenticationController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthenticationController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password/{token}', [AuthenticationController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('account.active')
        ->name('profile.edit');
    Route::post('/profile/personal', [ProfileController::class, 'updatePersonalInformation'])
        ->middleware('account.active')
        ->name('profile.personal.update');
    Route::post('/profile/contact', [ProfileController::class, 'updateContactInformation'])
        ->middleware('account.active')
        ->name('profile.contact.update');
    Route::post('/logout', [AuthenticationController::class, 'destroy'])->name('logout');
});
