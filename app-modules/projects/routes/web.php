<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/projects', 'projects::index')
        ->middleware('permission:projects.view')
        ->name('projects.index');

    Route::livewire('/projects/create', 'projects::form')
        ->middleware('permission:projects.create')
        ->name('projects.create');

    Route::livewire('/projects/{project}/edit', 'projects::form')
        ->middleware('permission:projects.update')
        ->name('projects.edit');
});
