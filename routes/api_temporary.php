<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'temporary'
], function () {
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::post('upload_copy_person_senasir', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'upload_copy_person_senasir']);
        Route::post('update_affiliate_id_person_senasir', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'update_affiliate_id_person_senasir']);
        Route::post('update_person_senasir_id', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'update_person_senasir_id']);
        Route::post('update_affiliate_id_senasir', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'update_affiliate_id_senasir']);
        Route::post('update_affiliate_id_senasir_registration_and_identity_card', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'update_affiliate_id_senasir_registration_and_identity_card']);
        Route::post('update_affiliate_data', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'update_affiliate_data']);
        Route::post('create_affiliate_spouse_senasir', [App\Http\Controllers\Temporary\CopyPersonSenasirController::class, 'create_affiliate_spouse_senasir']);
    });
});

