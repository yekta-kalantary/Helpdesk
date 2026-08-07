<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:settings.manage'])->group(function (): void {
    Route::livewire('/settings/smtp', 'settings::smtp')->name('settings.smtp.edit');
});
