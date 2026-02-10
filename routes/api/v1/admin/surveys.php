<?php

use App\Http\Controllers\Admin\SurveyController;
use Illuminate\Support\Facades\Route;

Route::middleware('require.permission:survey.manage')->group(function (): void {
    Route::apiResource('surveys', SurveyController::class);
    Route::post('/surveys/{survey}/assign', [SurveyController::class, 'assign']);
    Route::post('/surveys/{survey}/close', [SurveyController::class, 'close']);
    Route::get('/surveys/{survey}/analytics', [SurveyController::class, 'analytics']);
});
