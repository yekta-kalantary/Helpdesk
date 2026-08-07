<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\Presentation\Http\Controllers\TicketController;

Route::middleware('auth')->group(function (): void {
    Route::get('/tickets', [TicketController::class, 'index'])->middleware('permission:tickets.view')->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->middleware('permission:tickets.create')->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('permission:tickets.create')->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->middleware('permission:tickets.view')->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->middleware('permission:tickets.reply')->name('tickets.reply');
    Route::patch('/tickets/{ticket}/manage', [TicketController::class, 'manage'])->middleware('permission:tickets.manage')->name('tickets.manage');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->middleware('permission:tickets.delete')->name('tickets.destroy');
    Route::get('/tickets/{ticket}/messages/{message}/attachments/{media}', [TicketController::class, 'download'])->middleware('permission:tickets.view')->name('tickets.attachments.download');
});
