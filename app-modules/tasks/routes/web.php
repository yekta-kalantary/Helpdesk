<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Presentation\Http\Controllers\TaskAttachmentController;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/tasks', 'tasks::index')
        ->middleware('permission:tasks.view')
        ->name('tasks.index');

    Route::livewire('/tasks/create', 'tasks::form')
        ->middleware('permission:tasks.create')
        ->name('tasks.create');

    Route::livewire('/tasks/{task}', 'tasks::show')
        ->middleware('permission:tasks.view')
        ->name('tasks.show');

    Route::livewire('/tasks/{task}/edit', 'tasks::form')
        ->middleware('permission:tasks.update')
        ->name('tasks.edit');

    Route::get('/tasks/{task}/attachments/{media}', TaskAttachmentController::class)
        ->middleware('permission:tasks.view')
        ->name('tasks.attachments.download');
});
