<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'affiliate'
], function () {
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
       Route::get('/access_status/{id}', [App\Http\Controllers\Affiliate\AffiliateController::class, 'access_status']);
       Route::apiResource('/affiliate', App\Http\Controllers\Affiliate\AffiliateController::class)->only(['index','show']);
    });
});