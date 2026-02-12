<?php

use App\Http\Controllers\Admin\SurveyController;
use Illuminate\Support\Facades\Route;

Route::middleware('require.permission:survey.manage')->group(function (): void {
    Route::get('/surveys/target-students', [SurveyController::class, 'targetStudents']);
    Route::apiResource('surveys', SurveyController::class);
    Route::post('/surveys/{survey}/assign', [SurveyController::class, 'assign']);
    Route::post('/surveys/{survey}/close', [SurveyController::class, 'close']);
    Route::get('/surveys/{survey}/analytics', [SurveyController::class, 'analytics']);
});
