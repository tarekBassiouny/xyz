<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhooks\BunnyWebhookRequest;
use App\Services\Bunny\BunnyWebhookService;
use Illuminate\Http\JsonResponse;

class BunnyWebhookController extends Controller
{
    public function __construct(private readonly BunnyWebhookService $service) {}

    public function handle(BunnyWebhookRequest $request): JsonResponse
    {
        try {
            $this->service->handle($request->validate());
        } catch (\Throwable) {
            // Always return 200 per requirements
        }

        return response()->json(['success' => true]);
    }
}
