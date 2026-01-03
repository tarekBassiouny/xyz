<?php

use App\Http\Controllers\Admin\Course\CourseOperationController;
use App\Http\Controllers\Admin\PdfUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('require.permission:course.manage')->group(function (): void {
    Route::post('/courses/{course}/pdfs', [CourseOperationController::class, 'assignPdf']);
    Route::delete('/courses/{course}/pdfs/{pdf}', [CourseOperationController::class, 'removePdf']);
});

Route::post('/pdfs', [PdfUploadController::class, 'store'])
    ->middleware('require.permission:pdf.manage');
