<?php

use App\Http\Controllers\BugReportController;
use Illuminate\Support\Facades\Route;

Route::post('/bug-report', [BugReportController::class, 'store']);
Route::get('/bug-form', [BugReportController::class, 'form']);
