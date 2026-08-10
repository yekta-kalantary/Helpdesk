<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/tasks', 'tasks::index')->name('tasks.index');
    Route::livewire('/tasks/create', 'tasks::form')->name('tasks.create');
    Route::livewire('/tasks/{task}/edit', 'tasks::form')->name('tasks.edit');
});
