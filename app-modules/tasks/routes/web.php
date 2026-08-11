<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Presentation\Http\Controllers\AttachmentDownloadController;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/attachments/{attachment}', AttachmentDownloadController::class)->name('attachments.download');

    Route::livewire('/tasks', 'tasks::index')->name('tasks.index');
    Route::livewire('/tasks/create', 'tasks::form')->name('tasks.create');
    Route::livewire('/tasks/{task}/edit', 'tasks::form')->name('tasks.edit');
});
