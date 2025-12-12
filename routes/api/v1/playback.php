<?php

use App\Http\Controllers\Api\V1\PlaybackController;
use Illuminate\Support\Facades\Route;

Route::post('/courses/{course}/videos/{video}/playback/authorize', [PlaybackController::class, 'authorize']);
