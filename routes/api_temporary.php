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
    });
});

