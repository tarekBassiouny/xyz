<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Public\CenterDiscoveryController;
use App\Http\Controllers\Webhooks\BunnyWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/external/webhooks/bunny', [BunnyWebhookController::class, 'handle']);

Route::get('/external/centers/{slug}', [CenterDiscoveryController::class, 'show']);
