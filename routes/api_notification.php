<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
], function () {
    // Ruta para obtener los semestres
    Route::get('get_semesters', [App\Http\Controllers\Notification\NotificationController::class, 'get_semesters']);
    // Ruta para obtener las observaciones
    Route::get('get_observations/{module_id}', [App\Http\Controllers\Notification\NotificationController::class, 'get_observations']);
    // Ruta para obtener las modalidades de pago
    Route::get('get_modalities_payment', [App\Http\Controllers\Notification\NotificationController::class, 'get_modalities_payment']);
    // Ruta para notificación masiva
    Route::post('mass_notify', [App\Http\Controllers\Notification\NotificationController::class, 'mass_notification']);
});