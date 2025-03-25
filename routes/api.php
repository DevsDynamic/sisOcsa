<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/task', TaskController::class);
Route::get('/send-data-osinergmin', [TaskController::class, 'sendDataOsinergmin'])->name('osinergmins.send-data');
Route::get('/send-alert-wsp', [TaskController::class, 'checkAndSendAlerts'])->name('osinergmins.send-alert');
Route::get('/test-whatsapp', function () {
    $phone = '51921502571'; // Reemplaza con tu número de WhatsApp
    $message = '¡Hola! Este es un mensaje de prueba desde mi aplicación Laravel.';

    (new App\Http\Controllers\TaskController())->sendWhatsAppMessage($phone, $message);

    return 'Mensaje de prueba enviado.';
});