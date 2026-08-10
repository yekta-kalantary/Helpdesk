<?php

use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return auth()->check()
        ? redirect()->route('projects.index')
        : redirect()->route('login');
})->name('home');
