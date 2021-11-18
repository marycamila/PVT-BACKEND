<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

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
    Route::post('/login', [App\Http\Controllers\Api\Auth\AuthController::class, 'login']);
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::get('/profile', function(Request $request) {
            return auth()->user();
        });
        Route::delete('/logout', [App\Http\Controllers\Api\Auth\AuthController::class, 'logout']);
        Route::get('/auth', [App\Http\Controllers\Api\Auth\AuthController::class,'index']);
        Route::patch('/refresh', [App\Http\Controllers\Api\Auth\AuthController::class, 'refresh']);
        Route::apiResource('/role', App\Http\Controllers\Api\RoleController::class)->only(['index', 'show']);
        Route::apiResource('/module', App\Http\Controllers\Api\ModuleController::class)->only(['index', 'show']);
        Route::get('module/{module}/role', [App\Http\Controllers\Api\ModuleController::class, 'get_roles']);
        Route::apiResource('/permission', App\Http\Controllers\Api\PermissionController::class)->only(['index']);
        Route::apiResource('/user', App\Http\Controllers\Api\UserController::class)->only(['index', 'store','show']);
        Route::get('user/module_role_permision', [App\Http\Controllers\Api\UserController::class, 'module_role_permision']);
    });
});

