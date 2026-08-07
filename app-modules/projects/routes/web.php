<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Presentation\Http\Controllers\ProjectController;

Route::middleware('auth')->group(function (): void {
    Route::get('/projects', [ProjectController::class, 'index'])->middleware('permission:projects.view')->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->middleware('permission:projects.create')->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('permission:projects.create')->name('projects.store');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->middleware('permission:projects.update')->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->middleware('permission:projects.update')->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware('permission:projects.delete')->name('projects.destroy');
});
