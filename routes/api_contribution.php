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
        Route::post('/list_months_import_senasir_contribution', [App\Http\Controllers\Contribution\ImportContributionSenasirController::class, 'list_months_import_senasir_contribution']);
        Route::post('/rollback_copy_validate_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'rollback_copy_validate_senasir']);
        Route::post('/import_create_or_update_contribution_payroll_period_senasir ', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'import_create_or_update_contribution_payroll_period_senasir']);
        Route::post('/import_progress_bar', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'import_progress_bar']);
        Route::post('/list_months_validate_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'list_months_validate_senasir']);
    });
});

