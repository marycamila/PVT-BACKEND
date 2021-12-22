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
        Route::get('/command_payroll_period', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'command_payroll_period']);
        Route::post('/format_payroll_data_type_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'format_payroll_data_type_command']);
        Route::post('/update_base_wages', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'update_base_wages']);
    });
});

