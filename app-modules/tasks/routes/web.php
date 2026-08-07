<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Presentation\Http\Controllers\TaskController;

Route::middleware('auth')->group(function (): void {
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('permission:tasks.view')->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->middleware('permission:tasks.create')->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('permission:tasks.create')->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware('permission:tasks.view')->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->middleware('permission:tasks.update')->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('permission:tasks.update')->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->middleware('permission:tasks.update')->name('tasks.status.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware('permission:tasks.delete')->name('tasks.destroy');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'comment'])->middleware('permission:tasks.comment')->name('tasks.comments.store');
    Route::get('/tasks/{task}/attachments/{media}', [TaskController::class, 'download'])->middleware('permission:tasks.view')->name('tasks.attachments.download');
    Route::delete('/tasks/{task}/attachments/{media}', [TaskController::class, 'deleteAttachment'])->middleware('permission:tasks.update')->name('tasks.attachments.destroy');
});
