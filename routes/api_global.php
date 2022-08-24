<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'global'
], function () {
    //Rutas abiertas
    Route::patch('change_password', [App\Http\Controllers\Affiliate\AffiliateUserController::class, 'change_password']);
    Route::post('auth', [App\Http\Controllers\Affiliate\AffiliateUserController::class, 'auth']);
    Route::post('store', [App\Http\Controllers\Affiliate\AffiliateUserController::class, 'store']);
    Route::get('procedure_qr/{module_id}/{uuid}', [App\Http\Controllers\ProcedureQRController::class, 'procedure_qr']);
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::apiResource('city', App\Http\Controllers\CityController::class)->only(['index', 'show']);
    });
});

