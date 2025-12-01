<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\TokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Authentication (OTP + JWT)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    // Send OTP
    Route::post('/send-otp', [OtpController::class, 'send']);

    // Verify OTP -> Login
    Route::post('/verify', [LoginController::class, 'verify']);

    // Refresh JWT token
    Route::post('/refresh', [TokenController::class, 'refresh']);
});

/*
|--------------------------------------------------------------------------
| Admin (Sanctum Login)
|--------------------------------------------------------------------------
*/
Route::post('/admin/login', [AdminAuthController::class, 'login']);
