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
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
        Route::get('/profile', function(Request $request) {
            return auth()->user();
        });
        Route::delete('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
        Route::get('/auth', [App\Http\Controllers\Auth\AuthController::class,'index']);
        Route::patch('/refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
        Route::apiResource('/role', App\Http\Controllers\Admin\RoleController::class)->only(['index', 'show']);
        Route::apiResource('/module', App\Http\Controllers\Admin\ModuleController::class)->only(['index', 'show']);
        Route::get('module/{module}/role', [App\Http\Controllers\Admin\ModuleController::class, 'get_roles']);
        Route::get('role/{role}/role_permisions', [App\Http\Controllers\Admin\RoleController::class, 'role_permisions']);
        Route::apiResource('/permission', App\Http\Controllers\Admin\PermissionController::class)->only(['index']);
        Route::patch('role/{role}/permission', [App\Http\Controllers\Admin\RoleController::class,'set_or_remove_permission']);
        Route::apiResource('/user', App\Http\Controllers\Admin\UserController::class)->only(['index', 'store','show']);
        Route::get('user/module_role_permision', [App\Http\Controllers\Admin\UserController::class, 'module_role_permision']);
        Route::get('user/{user}/module_role_state_user', [App\Http\Controllers\Admin\UserController::class, 'module_role_state_user']);
        Route::patch('user/{user}/role', [App\Http\Controllers\Admin\UserController::class, 'set_or_remove_role']);
    });
});

