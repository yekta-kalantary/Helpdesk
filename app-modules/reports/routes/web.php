<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Presentation\Http\Controllers\ReportController;

Route::get('/reports', ReportController::class)
    ->middleware(['web', 'auth', 'permission:reports.view'])
    ->name('reports.index');
