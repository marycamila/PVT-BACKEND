<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'contribution'
], function () {
    // Rutas abiertas
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['auth:sanctum']
    ], function () {
       // Route::post('/upload_copy_payroll_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'upload_copy_payroll_command']);
        Route::get('/period_copy_payroll_upload_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'period_upload_command']);
        
    });
});

