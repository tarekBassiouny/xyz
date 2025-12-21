<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DeviceChangeRequestController;
use App\Http\Controllers\Api\V1\EnrollmentController;
use App\Http\Controllers\Api\V1\ExtraViewRequestController;
use App\Http\Controllers\Api\V1\PdfDownloadController;
use App\Http\Controllers\Api\V1\PlaybackController;
use App\Http\Controllers\Api\V1\PlaybackSessionController;
use App\Http\Controllers\Api\V1\Sections\PublicSectionController;
use App\Http\Controllers\Api\V1\Sections\PublicSectionPdfController;
use App\Http\Controllers\Api\V1\Sections\PublicSectionVideoController;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\MeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth (Public)
|--------------------------------------------------------------------------
*/
Route::post('/auth/send-otp', [AuthController::class, 'send']);
Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

/*
|--------------------------------------------------------------------------
| Authenticated Mobile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('jwt.mobile')->group(function (): void {

    Route::get('/auth/me', [MeController::class, 'profile']);
    Route::post('/auth/me', [MeController::class, 'updateProfile']);
    Route::post('/auth/logout', [MeController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Enrollments
    |--------------------------------------------------------------------------
    */
    Route::get('/enrollments', [EnrollmentController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show'])
        ->middleware('enrollment.active');

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    */
    Route::get('/courses/{course}/sections', [PublicSectionController::class, 'index'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/sections/{section}', [PublicSectionController::class, 'show'])
        ->middleware('enrollment.active');

    /*
    |--------------------------------------------------------------------------
    | Videos
    |--------------------------------------------------------------------------
    */
    Route::get('/courses/{course}/videos', [CourseController::class, 'listVideos'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/videos/{video}', [CourseController::class, 'showVideo'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/sections/{section}/videos', [PublicSectionVideoController::class, 'index'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/sections/{section}/videos/{video}', [PublicSectionVideoController::class, 'show'])
        ->middleware('enrollment.active');

    /*
    |--------------------------------------------------------------------------
    | PDFs
    |--------------------------------------------------------------------------
    */
    Route::get('/courses/{course}/pdfs', [CourseController::class, 'listPdfs'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/pdfs/{pdf}', [CourseController::class, 'showPdf'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/pdfs/{pdf}/download', PdfDownloadController::class)
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/sections/{section}/pdfs', [PublicSectionPdfController::class, 'index'])
        ->middleware('enrollment.active');
    Route::get('/courses/{course}/sections/{section}/pdfs/{pdf}', [PublicSectionPdfController::class, 'show'])
        ->middleware('enrollment.active');

    /*
    |--------------------------------------------------------------------------
    | Playback
    |--------------------------------------------------------------------------
    */
    Route::post('/courses/{course}/videos/{video}/playback/authorize', [PlaybackController::class, 'authorize']);
    Route::patch('/playback/sessions/{session}', [PlaybackSessionController::class, 'update']);
    Route::post('/playback/sessions/{session}/end', [PlaybackSessionController::class, 'end']);

    /*
    |--------------------------------------------------------------------------
    | Extra View Requests
    |--------------------------------------------------------------------------
    */
    Route::get('/extra-view-requests', [ExtraViewRequestController::class, 'index']);
    Route::post(
        '/courses/{course}/videos/{video}/extra-view-requests',
        [ExtraViewRequestController::class, 'store']
    );

    /*
    |--------------------------------------------------------------------------
    | Device Change Requests
    |--------------------------------------------------------------------------
    */
    Route::get('/device-change-requests', [DeviceChangeRequestController::class, 'index']);
    Route::post('/device-change-requests', [DeviceChangeRequestController::class, 'store']);
});
