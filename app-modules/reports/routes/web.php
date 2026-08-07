<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Presentation\Http\Controllers\ReportController;

Route::get('/reports', ReportController::class)
    ->middleware(['auth', 'permission:reports.view'])
    ->name('reports.index');
