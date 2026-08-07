<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Presentation\Http\Controllers\SmtpSettingsController;

Route::middleware(['auth', 'permission:settings.manage'])->group(function (): void {
    Route::get('/settings/smtp', [SmtpSettingsController::class, 'edit'])->name('settings.smtp.edit');
    Route::put('/settings/smtp', [SmtpSettingsController::class, 'update'])->name('settings.smtp.update');
});
