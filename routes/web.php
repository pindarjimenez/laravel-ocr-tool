<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/process-file', [App\Http\Controllers\HomeController::class, 'processFile']);
