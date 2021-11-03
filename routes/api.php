<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'pvt'
], function () {
    // Rutas abiertas
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::get('/profile', function(Request $request) {
            return auth()->user();
        });
        Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    });
});

