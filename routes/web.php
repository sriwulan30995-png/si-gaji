<?php

use App\Http\Controllers\Payroll\PayslipController;
use Illuminate\Support\Facades\Route;

Route::get('/payroll/{payroll}/download', [PayslipController::class, 'download'])
    ->name('payroll.download')
    ->middleware(['auth']);
