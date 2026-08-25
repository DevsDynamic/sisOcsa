<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/send-data-osinergmin/{environment?}', [TaskController::class, 'sendDataOsinergmin'])
    ->whereIn('environment', ['development', 'production'])
    ->middleware('throttle:1,1')
    ->name('osinergmins.send-data');
