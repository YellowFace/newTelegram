<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\IndexController::class, 'index']);
Route::get('/webhook', [\App\Http\Controllers\IndexController::class, 'webhook']);
