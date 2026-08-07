<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\Presentation\Http\Controllers\TicketAttachmentController;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::livewire('/tickets', 'tickets::index')
        ->middleware('permission:tickets.view')
        ->name('tickets.index');

    Route::livewire('/tickets/create', 'tickets::create')
        ->middleware('permission:tickets.create')
        ->name('tickets.create');

    Route::livewire('/tickets/{ticket}', 'tickets::show')
        ->middleware('permission:tickets.view')
        ->name('tickets.show');

    Route::get('/tickets/{ticket}/messages/{message}/attachments/{media}', TicketAttachmentController::class)
        ->middleware('permission:tickets.view')
        ->name('tickets.attachments.download');
});
