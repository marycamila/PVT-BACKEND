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
        Route::post('/rollback_payroll_copy_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'rollback_payroll_copy_command']);
        Route::post('/list_months_validate_command', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'list_months_validate_command']);
        Route::post('/import_payroll_command_progress_bar', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'import_payroll_command_progress_bar']);
        Route::post('/update_base_wages', [App\Http\Controllers\Contribution\ImportPayrollCommandController::class, 'update_base_wages']);
        Route::post('/list_months_import_contribution_command', [App\Http\Controllers\Contribution\ImportContributionCommandController::class, 'list_months_import_contribution_command']);
                      
        Route::post('/upload_copy_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'upload_copy_payroll_senasir']);
        Route::post('/validation_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'validation_payroll_senasir']);
        Route::post('/download_fail_not_found_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'download_fail_not_found_payroll_senasir']);
        Route::get('/list_senasir_years', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'list_senasir_years']);
        Route::post('/list_months_import_contribution_senasir', [App\Http\Controllers\Contribution\ImportContributionSenasirController::class, 'list_months_import_contribution_senasir']);
        Route::post('/rollback_payroll_copy_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'rollback_payroll_copy_senasir']);
        Route::post('/import_create_or_update_contribution_period_senasir ', [App\Http\Controllers\Contribution\ImportContributionSenasirController::class, 'import_create_or_update_contribution_period_senasir']);
        Route::post('/import_payroll_senasir_progress_bar', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'import_payroll_senasir_progress_bar']);
        Route::group([
            'middleware' => 'permission:show-affiliate|show-all-loan'
        ], function () {
            Route::post('/list_months_validate_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'list_months_validate_senasir']);
        });
        Route::post('/report_payroll_senasir', [App\Http\Controllers\Contribution\ImportPayrollSenasirController::class, 'report_payroll_senasir']);
        Route::post('/report_import_contribution_senasir', [App\Http\Controllers\Contribution\ImportContributionSenasirController::class, 'report_import_contribution_senasir']);        
    });
});

