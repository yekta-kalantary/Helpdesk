<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/projects', 'projects::index')->name('projects.index');
    Route::livewire('/projects/create', 'projects::form')->name('projects.create');
    Route::livewire('/projects/{project}/edit', 'projects::form')->name('projects.edit');
});
