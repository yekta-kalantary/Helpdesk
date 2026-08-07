<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/reports', 'reports::index')
    ->middleware(['web', 'auth', 'permission:reports.view'])
    ->name('reports.index');
