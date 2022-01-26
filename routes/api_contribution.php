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
        Route::post('/upload_copy_payroll_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'upload_copy_payroll_command']);
        Route::get('/command_payroll_period', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'command_payroll_period']);
        Route::post('/format_payroll_data_type_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'format_payroll_data_type_command']);
        Route::post('/update_base_wages', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'update_base_wages']);
        Route::post('/upload_copy_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'upload_copy_payroll_senasir']);
        Route::post('/validation_aid_contribution_affiliate_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'validation_aid_contribution_affiliate_payroll_senasir']);
        Route::post('/download_fail_validated_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'download_fail_validated_senasir']);
        Route::get('/list_senasir_years', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'list_senasir_years']);
        Route::post('/list_senasir_months', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'list_senasir_months']);
        Route::post('/rollback_copy_validate_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'rollback_copy_validate_senasir']);
    });
});

