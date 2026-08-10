<?php

use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return auth()->check()
        ? redirect()->route('contacts.index')
        : redirect()->route('login');
})->name('home');
