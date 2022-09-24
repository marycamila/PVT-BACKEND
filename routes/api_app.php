<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

Route::group([
    'middleware' => 'api',
    'prefix' => 'app'
], function () {
    // Rutas abiertas
    Route::get('procedure_qr/{module_id}/{uuid}', [App\Http\Controllers\ProcedureQRController::class, 'procedure_qr']);
    // Rutas autenticadas con token
    Route::group([
        'middleware' => ['api_auth']
    ], function () {
        Route::get('/get_information_loan/{id_affiliate}',[App\Http\Controllers\Loan\LoanController::class, 'get_information_loan']);
        Route::get('/all_contributions/{id}/{year}', [App\Http\Controllers\Contribution\AppContributionController::class, 'all_contributions']);
    });
});
