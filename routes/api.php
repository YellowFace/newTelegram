<?php

use Illuminate\Support\Facades\Route;

Route::any('/', [\App\Http\Controllers\IndexController::class, 'index']);
Route::any('/webhook', [\App\Http\Controllers\IndexController::class, 'webhook']);
