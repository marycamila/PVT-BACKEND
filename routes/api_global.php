<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'global'
], function () {
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth']
    ], function () {
        Route::apiResource('city', App\Http\Controllers\CityController::class)->only(['index', 'show']);
    });
});

