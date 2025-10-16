<?php

use App\Http\Controllers\Feature\AccountController;
use App\Http\Controllers\Feature\JournalEntryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', fn() => Inertia::render('Dashboard'))
        ->name('dashboard');

    Route::resource('accounts', AccountController::class);
    Route::resource('journal-entries', JournalEntryController::class)->except(['show']);
    Route::post('journal-entries/{entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
