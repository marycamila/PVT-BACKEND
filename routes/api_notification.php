<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api', 
    'prefix'     => 'notification'
], function () {
    // Ruta para obtener los semestres
    Route::get('get_semesters', [App\Http\Controllers\Notification\NotificationController::class, 'get_semesters']);
    // Ruta para obtener las observaciones
    Route::get('get_observations/{module_id}', [App\Http\Controllers\Notification\NotificationController::class, 'get_observations']);
    // Ruta para obtener las modalidades de pago
    Route::get('get_modalities_payment', [App\Http\Controllers\Notification\NotificationController::class, 'get_modalities_payment']);
    // Ruta para obtener los tipos de beneficiarios
    Route::get('get_beneficiary_type', [App\Http\Controllers\Notification\NotificationController::class, 'get_beneficiary_type']);
    // Ruta para obtener las jerarquias
    Route::get('get_hierarchical_level', [App\Http\Controllers\Notification\NotificationController::class, 'get_hierarchical_level']);
    // Ruta para obtener las acciones
    Route::get('get_actions', [App\Http\Controllers\Notification\NotificationController::class, 'get_actions']);
    // Ruta para obtener el listado masivo de afiliados
    Route::post('mass_notify', [App\Http\Controllers\Notification\NotificationController::class, 'mass_notification']);
    // Ruta para notificar
    Route::post('send_mass_notification', [App\Http\Controllers\Notification\NotificationController::class, 'send_mass_notification']);

});