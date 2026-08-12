<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Magna\Forms\Http\Controllers\FormSubmissionController;

/*
 * Public submission. Throttled per IP: a form endpoint is the most
 * attractive write surface on a public site, and the honeypot and timing
 * checks cost nothing to a flood that just wants to fill a table.
 */
Route::post('/forms/{handle}', [FormSubmissionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('forms.submit');
