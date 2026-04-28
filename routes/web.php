<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BugReportController;

// Страница с формой
Route::get('/issue/create', function () {
    return view('bug-report');
});

// Обработка формы
Route::post('/issue/store', [BugReportController::class, 'store'])->name('issues.store');

