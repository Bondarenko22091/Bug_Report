<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BugReportController;

Route::get('/issue/create', function () {
    return view('bug-report');
});

Route::post('/issue/store', [BugReportController::class, 'store'])->name('issues.store');

